<?php

namespace App\Http\Controllers;

use App\Custom\Json;
use App\Interfaces\FileHandlerInterface;
use App\Jobs\ProcessAccounts;
use App\Jobs\ProcessCreditCards;
use App\Models\AccountHolder;
use App\Models\CreditCard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;

class ImportData extends Controller
{
    const uploadDirectory = "customers";
    /**
     * Handle File import
     * @var FileHandlerInterface
     */
    protected $fileHandler;
    /**
     * Store a new user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    protected $request;
    /**
     * Save the account record
     * @var object
     */
    protected $accountHolder;
    /**
     * save the credit card record
     * @var object
     */
    protected $creditCard;
    /**
     * @var $lastAccountNumber
     */
    protected $lastAccountNumber;
    /**
     * @var string
     */
    protected $sort = "DESC";
    /**
     * @var array
     */
    protected $cards = [];
    /**
     * @var LazyCollection
     */
    protected LazyCollection $lazyCollection;
    /**
     * @var Collection
     */
    protected Collection $collection;
    /**
     * @var array
     */
    protected $accountHolders = [];

    /**
     * @param Request $request
     * @param string $sort
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function store(Request $request, string $sort = null, int $number = 0)
    {
        if ($file = $request->file('import_file')) {
            $fileType = $file->extension();
            $filePath = Storage::putFile(self::uploadDirectory, $file);
            switch ($fileType) {
                case "json":
                    $this->fileHandler = new Json($filePath);
                    break;
            }
        }

        if (!$this->fileHandler) {
            $this->fileHandler = new Json();
        }
        $this->lazyCollection = new LazyCollection($this->fileHandler->process());
//        $this->collection = new Collection($this->fileHandler->process());
        $this->accountHolders = $this->lazyCollection->whereNotNull('date_of_birth')
            ->reject('validateAge')
            ->sortBy('credit_card.number', SORT_NUMERIC, false)
            ->each(function ($customer, $key) {
                if ($customer->date_of_birth) {
                    try {
                        $customer->date_of_birth = Carbon::parse($customer->date_of_birth)->format('Y-m-d H:s:i');
                    } catch (\Exception $e) {
                        $customer->date_of_birth = Carbon::createFromFormat('d/m/Y',
                            $customer->date_of_birth)->format('Y-m-d H:s:i');
                    }
                }
                $this->lastAccountNumber = $customer->credit_card->number;
                $card = CreditCard::where('number', $customer->credit_card->number)
                    ->where('email', $customer->email)
                    ->where('account_number', $customer->account)->get()->values()->all();
                if(!$customer->email || !$customer->credit_card->expirationDate)
                dd($card, $customer);
                if (!$card) {
                    $this->accountHolder = AccountHolder::create([
                        "name"          => $customer->name,
                        "address"       => $customer->address,
                        "checked"       => $customer->checked,
                        "description"   => $customer->description,
                        "interest"      => $customer->interest,
                        "date_of_birth" => $customer->date_of_birth,
                        "email"         => $customer->email,
                        "account"       => $customer->account,
                    ]);
                    ProcessAccounts::dispatch($this->accountHolder)->onQueue('processing');
                    $this->creditCard = CreditCard::create([
                        "account_number" => $customer->account,
                        "type"           => $customer->credit_card->type,
                        "number"         => $customer->credit_card->number,
                        "name"           => $customer->credit_card->name,
                        "email"         => $customer->email,
                        "expirationDate" => $customer->credit_card->expirationDate,
                    ]);
                    ProcessCreditCards::dispatch($this->creditCard)->delay(now()->addSeconds(1))->onQueue('processing');
                }
            })
            ->pluck('credit_card.number')
            ->values()
            ->all();

        return view('processing', ['data' => count($this->accountHolders),'duplicates' => $this->lazyCollection->duplicates()]);
    }

    /**
     * @param string $dateOfBirth
     * @return bool
     */
    static function validateAge($account, $key): bool
    {
        $dateOfBirth = $account->date_of_birth;
        try {
            $dateOfBirth1 = Carbon::parse($dateOfBirth)->age;
            if ($dateOfBirth1 > 18 && $dateOfBirth1 < 65) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            dd($dateOfBirth, $key, $account);
        }
    }


    function removeProcessedRecords(object $accountNumber)
    {
        //sdd($accountNumber, $this->lastAccountNumber);
        return $this->sort == "DESC" ?
            $this->lastAccountNumber > (int)$accountNumber->credit_card->number :
            $this->lastAccountNumber < (int)$accountNumber->credit_card->numberr;
    }
}
