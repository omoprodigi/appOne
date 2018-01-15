<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Customer;
use App\Transaction;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class ChargeCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ChargeCustomers:chargecustomers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Charge customers due on this date';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $secretKey = 'sk_test_3582bc5c00d9f5d4f0b8e168e883c4d0f0f0a976';
        foreach (Customer::where('due_date', '>=', Carbon::now())->cursor() as $customer) {
            //
            $client = new Client(); //GuzzleHttp\Client
            $response = $client->post('https://api.paystack.co/transaction/charge_authorization', [
                'headers' => [
                    'authorization' => 'Bearer ' . $secretKey,
                    'content-type' => 'application/json'
                ],
                'json' => [
                    'authorization_code' => $customer->authCode,
                    'amount' => $customer->due_amount,
                    'email' => $customer->email
                ]
            ]);

            if ($response->getStatusCode() == 200) {
                $body = json_decode($response->getBody());
                $tranxReference = $body->data->reference;
                $transaction =  Transaction::create([
                    'amount' => $customer->due_amount,
                    'reference' => $tranxReference,
                    'customer_id' => $customer->id, 
                    'status' => 'pending'
                ]);
            } else {
                $customer->due_date = Carbon::now()->addDays(1);
                $customer->save();
            }

            $customer->save();
            // $reason = $response->getReasonPhrase(); // OK

            

        }

    }
}
