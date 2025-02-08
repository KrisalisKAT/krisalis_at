<?php

namespace App\Actions\MtgLister;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class GetScryfall
{
    use AsAction;

    const string BASE_URL = 'https://api.scryfall.com/';
    const string USER_AGENT = 'KatMtgLister';
    const string API_VERSION = 'v1';

    /**
     * @throws RequestException
     * @throws ConnectionException
     * @returns array
     */
    public function handle(string $path, array|string|null $query = null)
    {
        $response = Http::acceptJson()
            ->withUserAgent(self::USER_AGENT.'/'.self::API_VERSION)
            ->get(self::BASE_URL.$path, $query);
        if ($response->successful()) {
            return $response->json();
        } else {
            $response->throw();
        }
    }
}
