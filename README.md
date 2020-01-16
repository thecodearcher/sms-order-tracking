## Building an order tracking system in Laravel powered by Twilio SMS

In today's world where online shopping is at an all-time high, the chances of your e-commerce site standing out are going to be based on how much you can get your customers to trust in your service(s) and the level of satisfaction gotten from using them. One way of improving customer satisfaction and "trust" in your online shop is by allowing your customers to gain knowledge about the current state of their package. 

An order tracking system will allow your customers to gain more information about the current status/location of their package. Doing this will help increase the satisfaction of your customers, as they too will have detailed insight into where their package is at any given point in time until it arrives at their desired location.

In this tutorial, you will learn how to use [Twilio’s Programmable SMS](https://www.twilio.com/sms) to create an order tracking system using Laravel and update users about their package(s) via SMS.

## Prerequisite

In order to follow this tutorial, you will need:

- Basic knowledge of Laravel
- [Laravel](https://laravel.com/docs/master) installed on your local machine
- [Composer](https://getcomposer.org/) globally installed
- [MySQL](https://www.mysql.com/downloads/) setup on your local machine
- [Twilio Account](https://www.twilio.com/try-twilio?promo=B2YAW1)

## Project Setup

We will begin by creating a new Laravel project. This can be done either using the [Laravel installer](https://laravel.com/docs/5.8#installation) or [Composer](https://getcomposer.org/). In this tutorial we will be making use of the Laravel installer. If you don’t have it installed, you can learn how to set it up from the [Laravel documentation](https://laravel.com/docs/master). To generate a Laravel project using the Laravel Installer, run the following command on your terminal:

    $ laravel new order-tracking-sms

Next, set up a database for the application. For this tutorial, we will make use of a [MySQL](https://www.mysql.com/) database.  If you don't have MySQL installed on your local machine, head over to the [official site](https://www.mysql.com/downloads/) to get it installed on your platform of choice. After successful installation, open up your terminal and run the following to login to MySQL:

    $ mysql -u {your_user_name}

***NOTE:** Add the `-p` flag if you have a password for your MySQL instance.*

Once you are logged in, run the following command to create a new database:

    mysql> create database order-tracking;
    mysql> exit;

Next, update your `.env` file with your database credentials. Open up `.env` and make the following adjustments:

    DB_DATABASE=order-tracking
    DB_USERNAME={your_user_name}
    DB_PASSWORD={password if any}

Next, install the [Twilio SDK](https://www.twilio.com/docs/libraries/php) for PHP via [Composer](https://getcomposer.org/). Open up a terminal and run the following to install the Twilio SDK:

    $ composer require twilio/sdk

If you don’t have Composer installed on your local machine you can do so by following the instructions in [their documentation](https://getcomposer.org/doc/00-intro.md).

### Setting up Twilio SDK

After the successful installation of the [Twilio SDK](https://www.twilio.com/docs/libraries), you need to also fetch your Twilio credentials and your active [Twilio phone number](https://www.twilio.com/docs/phone-numbers) from your Twilio console. Head over to your [console](https://www.twilio.com/login) and grab your `account_sid` and `auth_token.`

![https://paper-attachments.dropbox.com/s_14AED1E729777868A76C728380D4E7434CFBFCFA0C71AD83ED009C3DCFE403E8_1574552733012_Group+8.png](https://paper-attachments.dropbox.com/s_14AED1E729777868A76C728380D4E7434CFBFCFA0C71AD83ED009C3DCFE403E8_1574552733012_Group+8.png)

Now navigate to the [Phone Number](https://www.twilio.com/console/phone-numbers/incoming) section to get your SMS enabled phone number.

![https://paper-attachments.dropbox.com/s_14AED1E729777868A76C728380D4E7434CFBFCFA0C71AD83ED009C3DCFE403E8_1574552749835_Group+9.png](https://paper-attachments.dropbox.com/s_14AED1E729777868A76C728380D4E7434CFBFCFA0C71AD83ED009C3DCFE403E8_1574552749835_Group+9.png)

If you don’t have an active number, you can easily create one [here](https://www.twilio.com/console/phone-numbers/search). This is the phone number you will use for sending and receiving SMS and also making phone calls via Twilio.

Next, update your `.env` file with the credentials. Open `.env` located at the root of the project directory and add these values:

    TWILIO_SID="INSERT YOUR TWILIO SID HERE"
    TWILIO_AUTH_TOKEN="INSERT YOUR TWILIO TOKEN HERE"
    TWILIO_NUMBER="INSERT YOUR TWILIO NUMBER IN [E.164] FORMAT"

## Mocking data

At this point, you should have your base project ready! In order to complete this tutorial you will need to create a table that will hold the mock orders data for the application. To create the orders table, run the following command to generate an [Eloquent model](https://laravel.com/docs/6.x/eloquent) alongside a migration file which will hold the definitions for the *orders* table:

    $ php artisan make:model Orders --migration

Now, open the `create_orders_table` migration file (`database/migrations/{timestamp}_create_orders_table.php`) and make the following changes:

    <?php
    
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    
    class CreateOrdersTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('orders', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('order_id');
                $table->string('current_location');
                $table->string('last_location');
                $table->string('status');
                $table->timestamps();
            });
        }
    
        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('orders');
        }
    }

Next, execute the migration to actually *commit* the changes to your database. To do this, open up a terminal and run the following:

    $ php artisan migrate

### Seeding Database

Next, you will need to setup [seeders](https://laravel.com/docs/5.8/seeding) for your database. This will be used to seed the *Orders* table with some sample data. To do this, generate a seeder class using the `artisan` command:

    $ php artisan make:seeder OrdersTableSeeder

Now, open up the just generated `database/seeds/OrdersTableSeeder.php` file and make the following changes:

    <?php
    
    use Faker\Generator as Faker;
    use Illuminate\Database\Seeder;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Str;
    
    class OrdersTableSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         *
         * @return void
         */
        public function run(Faker $faker)
        {
            DB::table('orders')->insert([
                [
                    'order_id' => Str::random(10),
                    'current_location' => $faker->streetAddress,
                    'last_location' => $faker->streetAddress,
                    'status' => "approved",
                ],
                [
                    'order_id' => Str::random(10),
                    'current_location' => $faker->streetAddress,
                    'last_location' => $faker->streetAddress,
                    'status' => "delivered",
                ],
                [
                    'order_id' => Str::random(10),
                    'current_location' => $faker->streetAddress,
                    'last_location' => $faker->streetAddress,
                    'status' => "in transit",
                ],
                [
                    'order_id' => Str::random(10),
                    'current_location' => $faker->streetAddress,
                    'last_location' => $faker->streetAddress,
                    'status' => "awaiting approval",
                ],
            ]);
        }
    }

This will create four dummy orders in your *Orders* table which will serve as the placed orders for this tutorial. Now open up your terminal and run the following to actually seed your database with the data:

    $ php artisan db:seed --class=OrdersTableSeeder

## Tracking Orders

At this point, you should have some test data in your database which will be used in the remaining part of this tutorial. First, generate a [controller](https://laravel.com/docs/6.x/controllers#introduction) which will hold the logic for the application. Open up your terminal and run the following `artisan` command to generate a controller class::

    $ php artisan make:controller OrderController

Now, open up  `app/Http/Controllers/OrderController.php`  and make the following changes:

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Orders;
    use Carbon\Carbon;
    use Illuminate\Http\Request;
    use Twilio\Rest\Client;
    
    class OrderController extends Controller
    {
        /**
         * Get a order using the order id.
         *
         * @param  Request  $request
         * @return Response
         */
        public function getOrder(Request $request)
        {
            $from = $request->input("From");
            $body = $request->input("Body");
            $order = Orders::where("order_id", $body)->first();
            if (!$order) {
                $response = "Invalid Order Id sent!";
            } else {
                $response = "Heres the current details of your order #{$order->order_id}: \n
    Current location: {$order->current_location} \n
    Previous location: {$order->last_location} \n
    Status: {$order->status} \n
    Arrival date: " . Carbon::tomorrow();
            }
            return $this->sendMessage($response, $from);
        }
    
        /**
         * Sends sms to user using Twilio's programmable sms client
         * @param string $message Body of sms
         * @param string|array $recipients string or array of phone number of recepient
         */
        private function sendMessage($message, $recipients)
        {
            $account_sid = getenv("TWILIO_SID");
            $auth_token = getenv("TWILIO_AUTH_TOKEN");
            $twilio_number = getenv("TWILIO_NUMBER");
            $client = new Client($account_sid, $auth_token);
            return $client->messages->create($recipients,
                array('from' => $twilio_number, 'body' => $message));
        }
    }

The `OrderController` now has two methods; `getOrder` and `sendMessage`. The `getOrder()` method will be called whenever an `order_id` is sent to your Twilio phone number and sends back an appropriate response back to the sender based on the content of the message body. This method (`getOrder`) gets the `Body` of the SMS and also the sender's phone number from the body of the request which is sent by Twilio after receiving an SMS. After retrieving the SMS data, the *Orders* table is queried with the `order_id` and after which a `$response` is sent back to the *sender* using the `sendMessage()` method depending on the results of the query. 

The `sendMessage()` method accepts two arguments; `message` and `recipients`. Internally, the `sendMessage()` method makes use of [Twilio programmable SMS](https://www.twilio.com/docs/sms) SDK for sending out text messages:

      /**
         * Sends sms to user using Twilio's programmable sms client
         * @param string $message Body of sms
         * @param string|array $recipients string or array of phone number of recepient
         */
        private function sendMessage($message, $recipients)
        {
            $account_sid = getenv("TWILIO_SID");
            $auth_token = getenv("TWILIO_AUTH_TOKEN");
            $twilio_number = getenv("TWILIO_NUMBER");
            $client = new Client($account_sid, $auth_token);
            return $client->messages->create($recipients,
                array('from' => $twilio_number, 'body' => $message));
        }

The *Twilio Client SDK* requires your *Twilio credentials* to be instantiated, using the inbuilt PHP [`getenv()`](https://www.php.net/manual/en/function.getenv.php) function, you can retrieve your Twilio credentials stored in your `.env` in the earlier parts of this tutorial. After creating an instance of the Twilio Client, you can then proceed to send an SMS by calling the `$client->messages->create()` method. This method accepts two arguments of a receiver which can either be a `string` or an `array` of phone numbers and an *array* with the properties of `from` and `body` where `from` is your active Twilio phone number and `body` is the text you want to be sent to the *recipients*. 

## Creating Routes

The next step is to make the methods we just created accessible via a route that calls your controller method. Open `routes/web.php` and make the following changes:

    <?php
    
    /*
    |--------------------------------------------------------------------------
    | Web Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register web routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | contains the "web" middleware group. Now create something great!
    |
    */
    
    use App\Orders;
    
    Route::get('/', function () {
        return Orders::all();
    });
    
    Route::post('/track-order', "OrderController@getOrder");

***NOTE:** The GET `/` route has been modified to return all the orders in your orders table as you will need to make use of an `order_id` to test your application.*

Before proceeding, you have to exclude your route from [CSRF protection](https://laravel.com/docs/6.x/csrf) by adding your `/order` route to the `except` array in `app/Http/Middleware/VerifyCsrfToken.php`:

    <?php
    
    namespace App\Http\Middleware;
    
    use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
    
    class VerifyCsrfToken extends Middleware
    {
        /**
         * Indicates whether the XSRF-TOKEN cookie should be set on the response.
         *
         * @var bool
         */
        protected $addHttpCookie = true;
    
        /**
         * The URIs that should be excluded from CSRF verification.
         *
         * @var array
         */
        protected $except = [
            "/track-order"
        ];
    }

## Setting up Twilio Webhook For Responding To SMS

As you might have figured, you will need a way to *alert* your application when an SMS is sent to your Twilio phone number. And one of the best ways to allow such communication to your application from external services is via webhooks. Twilio supports using webhooks to send an HTTP request to your application after an event occurs such as receiving an SMS or getting an incoming call depending on your configuration. Now to allow Twilio to send this *request* to your application, you must first configure your webhook URL from your Twilio console.

### Exposing Your Application To The Internet

Before your application can be accessed via a webhook, it must first be accessible remotely from the internet and not just your local machine. Luckily, this can easily be accomplished by using [ngrok](https://ngrok.com/). 

If you don’t have [ngrok](https://ngrok.com/) set up on your PC before now, quickly head over to their [official download page](https://ngrok.com/download) and follow the instructions to get it installed on your machine. If you already have it set up then open up your terminal and run the following commands to start your Laravel application and expose it to the internet:

    $ php artisan serve

 Now open another instance of your terminal and run this command:

    $ ngrok http 8000

***NOTE:** `8000` should be replaced with the port number which your Laravel application is running on.* 

After successful execution of the above command, you should see a screen similar to this:

![https://paper-attachments.dropbox.com/s_F7BA2EF37979C4BF44B5AA1B9207D8D3EC9EDDE27FB9D710DDC99DD2BCB47338_1560672098731_Screenshot+from+2019-06-16+08-57-28.png](https://paper-attachments.dropbox.com/s_F7BA2EF37979C4BF44B5AA1B9207D8D3EC9EDDE27FB9D710DDC99DD2BCB47338_1560672098731_Screenshot+from+2019-06-16+08-57-28.png)

Now, copy out your `forwarding` URL as this will be used shortly.

### Updating Twilio phone number configuration

Next, you need to update the webhook URL for your Twilio phone number' SMS configuration. This will allow Twilio make request to your application when an SMS message is received. Head over to the [active phone number](https://www.twilio.com/console/phone-numbers/incoming) section on your Twilio console and select your active phone number from the list which will be used as the phone number for receiving messages. Scroll down to the Messaging segment and update the webhook URL for the field labeled “A message comes in” as shown below:

![https://paper-attachments.dropbox.com/s_14AED1E729777868A76C728380D4E7434CFBFCFA0C71AD83ED009C3DCFE403E8_1574602377427_Group+12.png](https://paper-attachments.dropbox.com/s_14AED1E729777868A76C728380D4E7434CFBFCFA0C71AD83ED009C3DCFE403E8_1574602377427_Group+12.png)

## Testing

Awesome! Now you have both your application running and exposed to the web, you can proceed to carry out the final test. To do this, simply send a text message to your active Twilio number with any of the `order_id` (this can be gotten by opening the `/` route on your browser) and you should get a response back depending on the `order_id` you sent.

## Conclusion

At this point, you should have a working SMS based order tracking system. And with that, you have also learned how to make use of Laravel to accomplish this using Twilio’s programmable SMS and also how to expose your local server using ngrok. If you will like to take a look at the complete source code for this tutorial, you can find it on [Github.](https://github.com/thecodearcher/sms-order-tracking)

I’d love to answer any question(s) you might have concerning this tutorial. You can reach me via

- Email: [brian.iyoha@gmail.com](mailto:brian.iyoha@gmail.com)
- Twitter: [thecodearcher](https://twitter.com/thecodearcher)
- GitHub: [thecodearcher](https://github.com/thecodearcher)
