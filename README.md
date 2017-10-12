# Laravel Passport Multi-Authentication middleware

#### Laravel passport default behavior is to authenticate your `user` on the `users` table. 
#### While this is good enough for most of the apps, sometimes we need to tweak it a little bit if there is a new need arises.
#### I created this middleware because I need a few user groups that would access the app and in every user group there are roles.

# How to install

- In your terminal, run `composer require jsdecena/laravel-passport-multiauth`

- Add this line in your `config/app.php`

```
	'providers' => [
	    ...
	    Jsdecena\LPM\LaravelPassportMultiAuthServiceProvider::class,
	    ...
	]
```

- Add this in your `app\Http\Kernel.php`

```
    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        ...
        'mmda' => ProviderDetectorMiddleware::class,
    ];
```

- Also in your `routes/api.php`

```
    Route::post('oauth/token/', 'TokenAuthController@issueToken')
        ->middleware(['mmda', 'throttle'])
        ->name('issue.token');
```

> Trivia: Why mmda? This is because in the Philippines, they are the one that handles the traffic :sweat_smile: 

- And in the `config/auth.php`

```
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => 'App\User',
        ],
        /**
         * This is the important part. You can create as many providers as you like but right now, 
         * we just need the customer
         */
         'customers' => [
             'driver' => 'eloquent',
             'model' => App\Customer::class,
         ],
    ],
```

- And in your controller: `App\Http\Controllers\Auth\CustomerTokenAuthController.php`
 
 ```
 <?php
 
 namespace App\Http\Controllers\Auth;
 
 use App\Customers\Customer;
 use App\Customers\Exceptions\CustomerNotFoundException;
 use Illuminate\Database\QueryException;
 use Laravel\Passport\Http\Controllers\AccessTokenController;
 use Laravel\Passport\TokenRepository;
 use League\OAuth2\Server\AuthorizationServer;
 use Psr\Http\Message\ServerRequestInterface;
 use Lcobucci\JWT\Parser as JwtParser;
 
 class CustomerTokenAuthController extends AccessTokenController
 {
     /**
      * The authorization server.
      *
      * @var \League\OAuth2\Server\AuthorizationServer
      */
     protected $server;
 
     /**
      * The token repository instance.
      *
      * @var \Laravel\Passport\TokenRepository
      */
     protected $tokens;
 
     /**
      * The JWT parser instance.
      *
      * @var \Lcobucci\JWT\Parser
      */
     protected $jwt;
 
     /**
      * Create a new controller instance.
      *
      * @param  \League\OAuth2\Server\AuthorizationServer  $server
      * @param  \Laravel\Passport\TokenRepository  $tokens
      * @param  \Lcobucci\JWT\Parser  $jwt
      */
     public function __construct(AuthorizationServer $server,
                                 TokenRepository $tokens,
                                 JwtParser $jwt)
     {
         parent::__construct($server, $tokens, $jwt);
     }
 
     /**
      * Override the default Laravel Passport token generation
      *
      * @param ServerRequestInterface $request
      * @return array
      * @throws UserNotFoundException
      */
     public function issueToken(ServerRequestInterface $request)
     {
         $body = (parent::issueToken($request))->getBody()->__toString();
         $token = json_decode($body, true);
 
         if (array_key_exists('error', $token)) {
             return response()->json([
                 'error' => $token['error'],
                 'status_code' => 401
             ], 401);
         }
 
         try {
 
             $user = Customer::where('email', $request->getParsedBody()['username'])->first();
             return compact('token', 'user');
             
         } catch (QueryException $e) {
              return response()->json([
                  'error' => $token['error'],
                  'status_code' => 401
              ], 401);
         }
     }
 }
 ```
 
 > Note that you need the `Customer` model or any model that you need to authenticate with.

 - The request to authenticate must have the `provider` key so the system will know which user is to authenticate with
 
 eg.
 
```
POST /api/oauth/token HTTP/1.1
Host: localhost
Content-Type: application/x-www-form-urlencoded
Cache-Control: no-cache

grant_type=password&username=test%40email.com&password=secret&provider=customers
```