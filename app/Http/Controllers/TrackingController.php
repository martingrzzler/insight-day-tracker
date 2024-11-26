<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use GeoIp2\Database\Reader;
use Jenssegers\Agent\Agent;



class TrackingController extends Controller
{
    protected static $geoIpReader;

    public function __construct()
    {
        if (!self::$geoIpReader) {
            $ipDbPath = storage_path('app/private/GeoLite2-City.mmdb');
            self::$geoIpReader = new Reader($ipDbPath);
        }
    }

    public function track(Request $request)
    {
        $url = $request->query("url");
        if (!$url) {
            return response()->json(["error" => "url is required"], 400);
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json(["error" => "url is not valid"], 400);
        }

        $ip = $request->ip();

        $ip_record = null;
        $latitude = null;
        $longitude = null;
        try {
            // this definitely fails when the requests comes from a local network
            $ip_record = self::$geoIpReader->city($ip);
            $latitude = $ip_record->location->latitude;
            $longitude = $ip_record->location->longitude;
        } catch (\Exception $e) {
            Log::error("Failed to get ip record for ip " . $ip . " " . $e->getMessage());
        }

        $referer = $request->header("referer");
        $language = $request->header("accept-language");

        $agent = new Agent();


        if (!DB::insert(
            "insert into trackings
            (ip, latitude, longitude, url, referer, language, device, os, created_at)
            values (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$ip, $latitude, $longitude, $url, $referer, $language, $agent->device() ?: null, $agent->platform() ?: null, now()]
        )) {
            Log::error("Failed to insert tracking record");
            return response()->json(["error" => "Failed to insert tracking record"], 500);
        }

        return redirect($url);
    }
}
