<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $query = Device::with(['division'])->withCount('scanEvents');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('device_name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('mac_address', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($divisionId = $request->input('division_id')) {
            $query->where('division_id', $divisionId);
        }

        $devices = $query->orderBy('device_name')->paginate(10)->withQueryString();

        $divisions = Division::where('is_active', true)->orderBy('division_name')->get();

        $stats = [
            'total'       => Device::count(),
            'online'      => Device::where('status', 'online')->count(),
            'offline'     => Device::where('status', 'offline')->count(),
            'maintenance' => Device::where('status', 'maintenance')->count(),
        ];

        return view('admin.devices.index', [
            'devices'    => $devices,
            'divisions'  => $divisions,
            'stats'      => $stats,
            'search'     => $search ?? '',
            'status'     => $status ?? '',
            'divisionId' => $divisionId ?? '',
        ]);
    }

    public function create()
    {
        return redirect()->route('admin.devices.index');
    }

    public function show(string $id)
    {
        return redirect()->route('admin.devices.index');
    }

    public function edit(string $id)
    {
        return redirect()->route('admin.devices.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_name'  => 'required|string|max:100',
            'location'     => 'required|string|max:150',
            'division_id'  => 'nullable|uuid|exists:divisions,division_id',
            'mac_address'  => 'required|string|max:50|unique:devices,mac_address',
            'status'       => 'required|in:online,offline,maintenance',
            'installed_at' => 'nullable|date',
        ], [
            'device_name.required' => 'Nama alat reader wajib diisi.',
            'location.required'    => 'Lokasi / Plant penempatan alat wajib diisi.',
            'mac_address.required' => 'MAC address reader ESP32 wajib diisi.',
            'mac_address.unique'   => 'MAC address ini sudah terdaftar pada alat lain.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal_open', 'create');
        }

        // Generate plain API Key unik untuk perangkat ESP32
        $plainApiKey = 'af_dev_' . Str::random(32);

        $device = Device::create([
            'device_name'       => $request->input('device_name'),
            'location'          => $request->input('location'),
            'division_id'       => $request->input('division_id') ?: null,
            'mac_address'       => strtolower(trim($request->input('mac_address'))),
            'api_key_hash'      => Hash::make($plainApiKey),
            'status'            => $request->input('status', 'online'),
            'installed_at'      => $request->input('installed_at') ?: now()->toDateString(),
            'last_heartbeat_at' => $request->input('status') === 'online' ? now() : null,
        ]);

        return redirect()->route('admin.devices.index')->with([
            'success'              => "Alat reader '{$device->device_name}' berhasil didaftarkan.",
            'generated_device_id'  => $device->device_id,
            'generated_device_name'=> $device->device_name,
            'generated_mac'        => $device->mac_address,
            'generated_api_key'    => $plainApiKey,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $device = Device::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'device_name'  => 'required|string|max:100',
            'location'     => 'required|string|max:150',
            'division_id'  => 'nullable|uuid|exists:divisions,division_id',
            'mac_address'  => 'required|string|max:50|unique:devices,mac_address,' . $device->device_id . ',device_id',
            'status'       => 'required|in:online,offline,maintenance',
            'installed_at' => 'nullable|date',
        ], [
            'device_name.required' => 'Nama alat reader wajib diisi.',
            'location.required'    => 'Lokasi / Plant penempatan alat wajib diisi.',
            'mac_address.required' => 'MAC address reader wajib diisi.',
            'mac_address.unique'   => 'MAC address ini sudah digunakan alat lain.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal_open', 'edit')
                ->with('edit_device_id', $id);
        }

        $device->update([
            'device_name'  => $request->input('device_name'),
            'location'     => $request->input('location'),
            'division_id'  => $request->input('division_id') ?: null,
            'mac_address'  => strtolower(trim($request->input('mac_address'))),
            'status'       => $request->input('status'),
            'installed_at' => $request->input('installed_at') ?: $device->installed_at,
        ]);

        return redirect()->route('admin.devices.index')
            ->with('success', "Data alat '{$device->device_name}' berhasil diperbarui.");
    }

    public function regenerateKey(string $id)
    {
        $device = Device::findOrFail($id);

        $newPlainApiKey = 'af_dev_' . Str::random(32);

        $device->update([
            'api_key_hash' => Hash::make($newPlainApiKey),
        ]);

        return redirect()->route('admin.devices.index')->with([
            'success'              => "API Key baru untuk '{$device->device_name}' berhasil dibuat.",
            'generated_device_id'  => $device->device_id,
            'generated_device_name'=> $device->device_name,
            'generated_mac'        => $device->mac_address,
            'generated_api_key'    => $newPlainApiKey,
        ]);
    }

    public function destroy(string $id)
    {
        $device = Device::findOrFail($id);

        if ($device->scanEvents()->exists()) {
            // Jika sudah ada histori scan, jangan hapus fisik, tapi ubah status ke offline/maintenance
            $device->update(['status' => 'offline']);
            return redirect()->route('admin.devices.index')
                ->with('error', "Alat '{$device->device_name}' memiliki histori scan events terkait. Status otomatis diubah menjadi Offline untuk menjaga integritas data audit.");
        }

        $device->delete();

        return redirect()->route('admin.devices.index')
            ->with('success', "Alat '{$device->device_name}' berhasil dihapus dari sistem.");
    }
}
