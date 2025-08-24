<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Load the JSON file from public/data directory
     */
    protected function loadJson()
    {
        $path = public_path('data/rwanda-locations.json');
        $json = file_get_contents($path);

        return json_decode($json, true);
    }

    /**
     * Return all provinces (e.g. "Umujyi wa Kigali")
     */
    public function getProvinces()
    {
        $data = $this->loadJson();

        $provinces = array_map(function ($province) {
            return $province['name'];
        }, $data['provinces'] ?? []);

        return response()->json($provinces);
    }

    /**
     * Return districts for a given province name
     */
   public function getDistricts($provinceName)
{
    $data = $this->loadJson();

    foreach ($data['provinces'] as $province) {
        // 🔎 Debugging
        if ($province['name'] === $provinceName) {
            return response()->json(array_map(fn($d) => $d['name'], $province['districts']));
        }
    }

    // Add log for debugging
    logger("Province not matched: " . $provinceName);
    return response()->json([
        'message' => "Province '$provinceName' not found."
    ], 404);
}

    /**
     * Return sectors for a given district name
     */
    public function getSectors($districtName)
    {
        $data = $this->loadJson();

        foreach ($data['provinces'] as $province) {
            foreach ($province['districts'] ?? [] as $district) {
                if ($district['name'] === $districtName) {
                    return response()->json(
                        array_map(fn($s) => $s['name'], $district['sectors'] ?? [])
                    );
                }
            }
        }

        return response()->json([]);
    }

    /**
     * Return cells (akagari) for a given sector
     */
    public function getCells($sectorName)
    {
        $data = $this->loadJson();

        foreach ($data['provinces'] as $province) {
            foreach ($province['districts'] ?? [] as $district) {
                foreach ($district['sectors'] ?? [] as $sector) {
                    if ($sector['name'] === $sectorName) {
                        return response()->json(
                            array_map(fn($c) => $c['name'], $sector['cells'] ?? [])
                        );
                    }
                }
            }
        }

        return response()->json([]);
    }

    /**
     * Return villages for a given cell name
     */
    public function getVillages($cellName)
    {
        $data = $this->loadJson();

        foreach ($data['provinces'] as $province) {
            foreach ($province['districts'] ?? [] as $district) {
                foreach ($district['sectors'] ?? [] as $sector) {
                    foreach ($sector['cells'] ?? [] as $cell) {
                        if ($cell['name'] === $cellName) {
                            return response()->json(
                                array_map(fn($v) => $v['name'], $cell['villages'] ?? [])
                            );
                        }
                    }
                }
            }
        }

        return response()->json([]);
    }

    /**
     * Optional: Test route
     */
    public function hello()
    {
        return response()->json(['message' => 'API is working 👍']);
    }
}