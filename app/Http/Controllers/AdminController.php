<?php

namespace App\Http\Controllers;

use App\Models\Adoption;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    // --- DASHBOARD OVERVIEW (STATISTIK & AKTIVITAS) ---
    public function getDashboardOverview()
    {
        // 1. Hitung Statistik
        $stats = [
            'pending_donations' => Pet::where('status', 'pending_review')->count(),
            'pending_adoptions' => Adoption::where('status', 'pending')->count(),
            'total_pets' => Pet::where('status', 'available')->count(),
            'total_users' => \App\Models\User::count(),
        ];

        // 2. Ambil 5 Donasi Terakhir (Apapun statusnya)
        $recent_donations = Pet::with('user')->latest()->take(5)->get()->map(function($item) {
            return [
                'type' => 'donation',
                'user' => $item->user->name ?? 'Unknown',
                'desc' => "Mendonasikan {$item->name} ({$item->species})",
                'date' => $item->created_at,
                'status' => $item->status // Kirim status juga biar bisa diwarnai
            ];
        });

        // 3. Ambil 5 Adopsi Terakhir (Apapun statusnya)
        $recent_adoptions = Adoption::with(['user', 'pet'])->latest()->take(5)->get()->map(function($item) {
            return [
                'type' => 'adoption',
                'user' => $item->user->name ?? 'Unknown',
                'desc' => "Request adopsi " . ($item->pet->name ?? 'Hewan'),
                'date' => $item->created_at,
                'status' => $item->status
            ];
        });

        // 4. Gabung & Sortir berdasarkan waktu terbaru
        $activities = $recent_donations->merge($recent_adoptions)->sortByDesc('date')->values()->take(5);

        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => $stats,
                'activities' => $activities
            ]
        ]);
    }
    // --- MANAJEMEN DONASI (HEWAN) ---

    public function getPendingDonations()
    {
        // Mengambil user dan images (foto tambahan)
        $pets = Pet::with(['user', 'images']) 
            ->where('status', 'pending_review')
            ->latest()
            ->get();

        return response()->json(['status' => 'success', 'data' => $pets]);
    }

    public function approveDonation($id)
    {
        $pet = Pet::find($id);
        if (!$pet) return response()->json(['message' => 'Not found'], 404);

        $pet->update(['status' => 'available']);

        return response()->json(['status' => 'success', 'message' => 'Donasi disetujui.']);
    }

    public function rejectDonation(Request $request, $id)
    {
        $pet = Pet::find($id);
        if (!$pet) return response()->json(['message' => 'Not found'], 404);

        // Validasi alasan wajib diisi
        $request->validate([
            'admin_note' => 'required|string|min:5',
        ]);

        $pet->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note // Simpan alasan
        ]);

        return response()->json(['status' => 'success', 'message' => 'Donasi ditolak dengan alasan.']);
    }

    // UPDATE DATA HEWAN (ADMIN EDIT)
    public function updatePet(Request $request, $id)
    {
        $pet = Pet::find($id);
        if (!$pet) return response()->json(['message' => 'Not found'], 404);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'breed' => 'nullable|string',
            'age' => 'nullable|integer',
            'description' => 'required|string',
            'health_status' => 'required|string',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $pet->update($request->only(['name', 'breed', 'age', 'description', 'health_status']));

        return response()->json(['status' => 'success', 'message' => 'Data hewan berhasil diperbarui.']);
    }

    // --- MANAJEMEN ADOPSI ---

    public function getPendingAdoptions()
    {
        $adoptions = Adoption::with(['user', 'pet'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return response()->json(['status' => 'success', 'data' => $adoptions]);
    }

    public function approveAdoption($id)
    {
        $adoption = Adoption::with('pet')->find($id);
        if (!$adoption) return response()->json(['message' => 'Not found'], 404);

        $adoption->update(['status' => 'approved']);

        if ($adoption->pet) {
            $adoption->pet->update(['status' => 'adopted']);
        }

        return response()->json(['status' => 'success', 'message' => 'Adopsi disetujui.']);
    }

    public function rejectAdoption(Request $request, $id)
    {
        $adoption = Adoption::find($id);
        if (!$adoption) return response()->json(['message' => 'Not found'], 404);

        $adoption->update([
            'status' => 'rejected',
            'admin_note' => $request->input('admin_note')
        ]);

        return response()->json(['status' => 'success', 'message' => 'Adopsi ditolak.']);
    }

    // --- MASTER DATA (SEMUA HEWAN) ---
    public function getAllPets()
    {
        $pets = Pet::with([
            'user',       
            'images',     
            'adoptions' => function($query) {
                $query->where('status', 'approved')->with('user');
            }
        ])
        ->latest()
        ->get();

        return response()->json(['status' => 'success', 'data' => $pets]);
    }

    public function deletePet($id)
    {
        $pet = Pet::find($id);
        if (!$pet) return response()->json(['message' => 'Not found'], 404);
        
        $pet->delete();

        return response()->json(['status' => 'success', 'message' => 'Data hewan berhasil dihapus permanen.']);
    }
}