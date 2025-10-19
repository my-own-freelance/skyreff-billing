<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class BroadcastHelper
{
  /**
   * Kirim broadcast WA
   *
   * @param string $to Nomor tujuan (contoh: 08123456789)
   * @param string $message Pesan yang akan dikirim
   * @return array Response API
   */
  public static function send($to, $message)
  {
    $payload = [
      "appkey"   => "80feae0d-f841-4f3a-a7b3-5349ba2d73e0",
      "authkey"  => "j8znJb83n04XeenAPuVEOxZWRKX62DWTHpFEHaRgP1WtdUR972",
      "to"       => preg_replace('/^08/', '628', $to), // ubah ke format 62
      "message"  => $message,
    ];

    $response = Http::post('https://app.saungwa.com/api/create-message', $payload);

    return $response->json();
  }

  /**
   * Parse template content dengan data dinamis
   *
   * @param string $templateContent
   * @param array $data key = nama variabel tanpa #, value = isi
   * @return string
   */
  public static function parseTemplate(string $templateContent, array $data)
  {
    foreach ($data as $key => $value) {
      $templateContent = str_replace("#{$key}#", $value, $templateContent);
    }

    return $templateContent;
  }
}
