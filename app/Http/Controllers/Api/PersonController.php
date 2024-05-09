<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PersonController extends Controller
{
    public function getAllData()
    {
        try {
            $people = Person::all()->map(function ($person) {
                if ($person->gambar) {
                    $person->gambar = url('storage/' . substr($person->gambar, 7));
                }
                return $person;
            });

            return response()->json(
                ['people' => $people],
                200
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama' => 'required',
                'email' => 'required|email|unique:people,email',
                'alamat' => 'required',
                'no_telpon' => 'required',
            ]);

            $path = $request->file('gambar')->store('public/images');

            $person = new Person([
                'nama' => $request->get('nama'),
                'email' => $request->get('email'),
                'alamat' => $request->get('alamat'),
                'no_telpon' => $request->get('no_telpon'),
                'gambar' => $path,
            ]);

            $person->save();

            return response()->json([
                'message' => 'Data Person berhasil ditambahkan',
                'people' => $person
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'nama' => 'required',
                'email' => 'required',
                'alamat' => 'required',
                'no_telpon' => 'required',
            ]);

            $person = Person::findOrFail($id);

            // Update data
            $person->nama = $request->get('nama');
            $person->email = $request->get('email');
            $person->alamat = $request->get('alamat');
            $person->no_telpon = $request->get('no_telpon');

            // Update gambar if provided
            if ($request->hasFile('gambar')) {
                // Delete existing image
                if ($person->gambar) {
                    Storage::delete($person->gambar);
                }

                // Store new image
                $path = $request->file('gambar')->store('public/images');
                $person->gambar = $path;
            }

            $person->save();

            return response()->json([
                'message' => 'Data Person berhasil diupdate',
                'people' => $person
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $person = Person::findOrFail($id);

            if ($person->gambar) {
                Storage::delete($person->gambar);
            }

            $person->delete();

            return response()->json([
                'message' => 'Data Person berhasil dihapus'
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation errors',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
