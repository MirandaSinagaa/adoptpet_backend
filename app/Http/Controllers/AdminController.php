<?php

namespace App\Http\Controllers;

use App\Models\Adoption;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    // =========================================
    // DASHBOARD OVERVIEW
    // =========================================
    public function getDashboardOverview()
    {
        $stats = [
            'pending_donations' => Pet::where('status', 'pending_review')->count(),
            'pending_adoptions' => Adoption::where('status', 'pending')->count(),
            'total_pets' => Pet::where('status', 'available')->count(),
            'total_users' => User::where('role', 'user')->count(),
        ];

        $users = User::where('role', 'user')->latest()->take(5)->get()->map(function ($user) {
            return [
                'type' => 'register',
                'user' => $user->name,
                'desc' => 'Bergabung sebagai pengguna baru 🎉',
                'date' => $user->created_at,
                'status' => 'new_user'
            ];
        });

        $pets = Pet::with('user')->orderBy('updated_at', 'desc')->take(5)->get()->map(function ($pet) {
            $type = 'update';
            $desc = 'Update data hewan';

            if ($pet->status === 'pending_review') {
                $type = 'donation';
                $desc = "Mendonasikan {$pet->name}";
            } elseif ($pet->status === 'available') {
                $type = 'approve';
                $desc = "{$pet->name} disetujui ✅";
            } elseif ($pet->status === 'rejected') {
                $type = 'reject';
                $desc = "{$pet->name} ditolak ❌";
            } elseif ($pet->status === 'canceled') {
                $type = 'cancel';
                $desc = "{$pet->name} dibatalkan";
            } elseif ($pet->status === 'adopted') {
                $type = 'success';
                $desc = "{$pet->name} berhasil diadopsi 🏡";
            }

            return [
                'type' => $type,
                'user' => $pet->user->name ?? 'Anonim',
                'desc' => $desc,
                'date' => $pet->updated_at,
                'status' => $pet->status
            ];
        });

        $adoptions = Adoption::with(['user', 'pet'])->orderBy('updated_at', 'desc')->take(5)->get()->map(function ($adoption) {
            $type = 'update';
            $desc = 'Update adopsi';
            $petName = $adoption->pet->name ?? 'Hewan';

            if ($adoption->status === 'pending') {
                $type = 'adoption_request';
                $desc = "Ingin mengadopsi {$petName} 💌";
            } elseif ($adoption->status === 'approved') {
                $type = 'adoption_approve';
                $desc = "Disetujui adopsi {$petName} 🤝";
            } elseif ($adoption->status === 'rejected') {
                $type = 'adoption_reject';
                $desc = "Adopsi {$petName} ditolak ❌";
            }

            return [
                'type' => $type,
                'user' => $adoption->user->name ?? 'Unknown',
                'desc' => $desc,
                'date' => $adoption->updated_at,
                'status' => $adoption->status
            ];
        });

        $activities = $users->merge($pets)->merge($adoptions)->sortByDesc('date')->values()->take(8);

        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => $stats,
                'activities' => $activities
            ]
        ]);
    }

    // =========================================
    // DONATIONS
    // =========================================
    public function getPendingDonations()
    {
        $pets = Pet::with(['user', 'images'])
            ->where('status', 'pending_review')
            ->latest()
            ->get();

        return response()->json(['status' => 'success', 'data' => $pets]);
    }

    public function approveDonation($id)
    {
        $pet = Pet::find($id);
        if (!$pet) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $pet->update(['status' => 'available']);
        return response()->json(['status' => 'success', 'message' => 'Donasi berhasil disetujui.']);
    }

    public function rejectDonation(Request $request, $id)
    {
        $request->validate(['admin_note' => 'required|string|min:5']);

        $pet = Pet::find($id);
        if (!$pet) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $pet->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note
        ]);

        return response()->json(['status' => 'success', 'message' => 'Donasi berhasil ditolak.']);
    }

    // =========================================
    // UPDATE DATA HEWAN (PERBAIKAN: TAMBAH SPECIES)
    // =========================================
    public function updatePet(Request $request, $id)
    {
        $pet = Pet::find($id);
        if (!$pet) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'species' => 'required|in:cat,dog,other', // Validasi Species
            'breed' => 'nullable|string|max:255',
            'age' => 'nullable|integer',
            'description' => 'required|string',
            'health_status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update semua field termasuk species
        $pet->update($request->only(['name', 'species', 'breed', 'age', 'description', 'health_status']));

        return response()->json([
            'status' => 'success',
            'message' => 'Data hewan berhasil diperbarui.',
            'data' => $pet
        ]);
    }

    // =========================================
    // ADOPTIONS
    // =========================================
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
        if (!$adoption) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $adoption->update(['status' => 'approved']);

        if ($adoption->pet) {
            $adoption->pet->update(['status' => 'adopted']);
        }

        return response()->json(['status' => 'success', 'message' => 'Adopsi berhasil disetujui.']);
    }

    public function rejectAdoption(Request $request, $id)
    {
        $adoption = Adoption::find($id);
        if (!$adoption) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $adoption->update([
            'status' => 'rejected',
            'admin_note' => $request->input('admin_note')
        ]);

        return response()->json(['status' => 'success', 'message' => 'Adopsi berhasil ditolak.']);
    }

    // =========================================
    // MASTER PETS
    // =========================================
    public function getAllPets()
    {
        $pets = Pet::with([
            'user',
            'images',
            'adoptions' => function ($query) {
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
        if (!$pet) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $pet->delete();
        return response()->json(['status' => 'success', 'message' => 'Data hewan berhasil dihapus.']);
    }
}