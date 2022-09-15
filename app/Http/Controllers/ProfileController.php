<?php

namespace App\Http\Controllers;

use App\PlatformData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    private $pageLinks;

    public function __construct()
    {
        $this->middleware('auth');

        // Compute an array of all of the links to other pages in the profile group.
        $allRoutes = collect(Route::getroutes()->get());
        $allRoutes->filter(function (\Illuminate\Routing\Route $route) {
            return strpos($route->uri, 'profile') === 0;
        })->each(function (\Illuminate\Routing\Route $route) {
            if (array_key_exists('as', $route->action)) {
                $page = $route->action['as'];
                $this->pageLinks[$page] = "/$route->uri";
            }
        });
    }

    public function index()
    {
        return view('pages.profile.index', [
            'pageLinks' => json_encode($this->pageLinks),
        ]);
    }

    public function robinhood()
    {
        $account = Auth::user()->platforms()->where('platform', 'robinhood')->first();

        return view('pages.profile.robinhood', [
            'username'  => $account ? $account->username : '',
            'updatedAt' => $account ? $account->updated_at : 'Never',
            'pageLinks' => json_encode($this->pageLinks),
        ]);
    }

    protected function robinhoodValidator(array $data)
    {
        return Validator::make($data, [
            'username'     => ['required', 'string', 'max:255'],
            'password'     => ['required', 'string'],
        ]);
    }

    private function addPortfolio(PlatformData $platform, $user)
    {
        if ($user->portfolio) {
            if ($user->portfolio->platform_data_id !== $platform->id) {
                throw new \Exception('Portfolio / Profile Mismatch');
            }
        } else {
            $user->portfolio()->create(['platform_data_id' => $platform->id]);
        }
    }

    public function saveRobinhood(Request $request)
    {
        $this->robinhoodValidator($request->all())->validate();

        $user = Auth::user();
        $platforms = $user->platforms();
        $username = $request->input('username');
        $password = encrypt($request->input('password'));
        $platform = $platforms->where('platform', 'robinhood')->first();

        if ($platform !== null) {
            $platform->username = $username;
            $platform->password = $password;
            $platform->save();
        } else {
            $platform = $platforms->create([
                'platform' => 'robinhood',
                'username' => $username,
                'password' => $password,
            ]);
        }

        $this->addPortfolio($platform, $user);

        return redirect(route('profile.robinhood'));
    }

    public function alphaVantage()
    {
        $dataSource = Auth::user()->dataSource;

        return view('pages.profile.alphavantage', [
            'apiKey'    => $dataSource ? $dataSource->api_key : '',
            'updatedAt' => $dataSource ? $dataSource->updated_at : 'Never',
            'pageLinks' => json_encode($this->pageLinks),
        ]);
    }

    protected function alphaVantageValidator(array $data)
    {
        return Validator::make($data, [
            'api-key'     => ['required', 'string'],
        ]);
    }

    public function saveAlphaVantage(Request $request)
    {
        $this->alphaVantageValidator($request->all())->validate();

        $alphaVantageApis = Auth::user()->dataSource();
        $apiKey = $request->input('api-key');

        if ($alphaVantageApi = $alphaVantageApis->first()) {
            $alphaVantageApi->api_key = $apiKey;
            $alphaVantageApi->updated_at = Carbon::now();
            $alphaVantageApi->save();
        } else {
            $alphaVantageApi = $alphaVantageApis->create([
                'api_key' => $apiKey,
            ]);
        }

        return redirect(route('profile.alpha-vantage'));
    }
}
