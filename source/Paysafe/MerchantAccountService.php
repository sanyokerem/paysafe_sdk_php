<?php

namespace Paysafe;

use Paysafe\AccountManagement\Merchant;
use Paysafe\AccountManagement\MerchantAccount;
use Paysafe\AccountManagement\MerchantAccountAddress;
use Paysafe\AccountManagement\MerchantAccountBusinessOwner;
use Paysafe\AccountManagement\MerchantAccountBusinessOwnerAddress;
use Paysafe\AccountManagement\MerchantAccountBusinessOwnerIdentityDocument;
use Paysafe\AccountManagement\MerchantAchBankAccount;
use Paysafe\AccountManagement\MerchantEftBankAccount;
use Paysafe\AccountManagement\MerchantSubAccount;
use Paysafe\AccountManagement\RecoveryQuestion;
use Paysafe\AccountManagement\RecoveryQuestionsList;
use Paysafe\AccountManagement\TermsAndConditions;
use Paysafe\AccountManagement\User;

class MerchantAccountService
{

    /**
     * @var PaysafeApiClient
     */
    private $client;

    /**
     * The uri for the customer vault api.
     * @var string
     */
    private $uri = "accountmanagement/v1";

    /**
     * @param \Paysafe\PaysafeApiClient $client
     */
    public function __construct(PaysafeApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * Monitor.
     *
     * @return bool true if successful
     * @throws PaysafeException
     */
    public function monitor()
    {
        $request = new Request(array(
            'method' => Request::GET,
            'uri' => 'accountmanagement/monitor'
        ));

        $response = $this->client->processRequest($request);
        if (!isset($response['status'])) {
            return false;
        }
        return ($response['status'] == 'READY');
    }

    /**
     * Create Merchant Account
     *
     * @param MerchantAccount $merchantAccount
     * @return MerchantAccount
     * @throws PaysafeException
     */
    public function createMerchantAccount(MerchantAccount $merchantAccount)
    {
        $merchantAccount->setRequiredFields(array(
            'name',
            'currency',
            'region',
            'legalEntity',
            'productCode',
            'category',
            'phone',
            'yearlyVolumeRange',
            'averageTransactionAmount',
            'merchantDescriptor'
        ));
        $merchantAccount->setOptionalFields(array(
            'merchantId',
            'caAccountDetails',
            'usAccountDetails',
        ));

        $request = new Request(array(
            'method' => Request::POST,
            'uri' => $this->prepareURI('/merchants/' . $merchantAccount->merchantId . '/accounts'),
            'body' => $merchantAccount
        ));
        $response = $this->client->processRequest($request);

        return new MerchantAccount($response);
    }

    /**
     * Create New User
     *
     * @param User $user
     * @return User
     * @throws PaysafeException
     */
    public function createNewUser(User $user)
    {
        $user->setRequiredFields(array(
            'userName',
            'password',
            'email',
            'recoveryQuestion',
        ));

        $request = new Request(array(
            'method' => Request::POST,
            'uri' => $this->prepareURI('/accounts/' . $this->client->getAccount() . '/users'),
            'body' => $user
        ));
        $response = $this->client->processRequest($request);

        return new User($response);
    }

    /**
     * Get Recovery Questions
     *
     * @return RecoveryQuestionsList
     * @throws PaysafeException
     */
    public function getRecoveryQuestions()
    {
        $request = new Request(array(
            'method' => Request::GET,
            'uri' => $this->prepareURI('/recoveryquestions')
        ));
        $response = $this->client->processRequest($request);

        return new RecoveryQuestionsList($response);
    }

    /**
     * Prepare the uri for submission to the api.
     *
     * @param type $path
     * @return string uri
     * @throw PaysafeException
     */
    private function prepareURI($path)
    {
        return $this->uri . $path;
    }

    /**
     * Create Merchant Account Address
     *
     * @param MerchantAccountAddress $address
     * @return MerchantAccountAddress
     * @throws PaysafeException
     */
    public function createMerchantAccountAddress(MerchantAccountAddress $address)
    {
        $address->setRequiredFields(array(
            'street',
            'city',
            'state',
            'country',
            'zip'
        ));
        $address->setOptionalFields(array(
            'street2'
        ));
        $this->fixState($address);
        $request = new Request(array(
            'method' => Request::POST,
            'uri' => $this->prepareURI('/accounts/' . $this->client->getAccount() . '/addresses'),
            'body' => $address
        ));
        $response = $this->client->processRequest($request);

        return new MerchantAccountAddress($response);
    }

    /**
     * Create Merchant Account Business Owner
     *
     * @param MerchantAccountBusinessOwner $businessOwner
     * @return MerchantAccountBusinessOwner
     * @throws PaysafeException
     */
    public function createMerchantAccountBusinessOwner(MerchantAccountBusinessOwner $businessOwner)
    {
        $businessOwner->setRequiredFields(array(
            'firstName',
            'lastName',
            'jobTitle',
            'phone',
            'dateOfBirth',
            'dateOfBirth'
        ));
        $businessOwner->setOptionalFields(array(
            'middleName',
            'email',
            'ssn',
        ));
        $request = new Request(array(
            'method' => Request::POST,
            'uri' => $this->prepareURI('/accounts/' . $this->client->getAccount() . '/businessowners'),
            'body' => $businessOwner
        ));
        $response = $this->client->processRequest($request);

        return new MerchantAccountBusinessOwner($response);
    }

    /**
     * Create Merchant Account Business Owner Address
     *
     * @param MerchantAccountBusinessOwnerAddress $businessOwnerAddress
     * @return MerchantAccountBusinessOwnerAddress
     * @throws PaysafeException
     */
    function createMerchantAccountBusinessOwnerAddress(MerchantAccountBusinessOwnerAddress $businessOwnerAddress)
    {
        $businessOwnerAddress->setRequiredFields(array(
            'businnessOwnerId',
            'street',
            'city',
            'state',
            'country',
            'zip',
            'yearsAtAddress',
        ));
        $businessOwnerAddress->setOptionalFields(array(
            'street2'
        ));
        $this->fixState($businessOwnerAddress);
        $request = new Request(array(
            'method' => Request::POST,
            'uri' => $this->prepareURI('/businessowners/' . $businessOwnerAddress->businnessOwnerId . '/currentaddresses'),
            'body' => $businessOwnerAddress
        ));
        $response = $this->client->processRequest($request);

        return new MerchantAccountBusinessOwnerAddress($response);
    }

    /**
     * Create Merchant Account Business Owner Address Previous
     *
     * @param MerchantAccountBusinessOwnerAddress $businessOwnerAddress
     * @return MerchantAccountBusinessOwnerAddress
     * @throws PaysafeException
     */
    function createMerchantAccountBusinessOwnerAddressPrevious(MerchantAccountBusinessOwnerAddress $businessOwnerAddress)
    {
        $businessOwnerAddress->setRequiredFields(array(
            'businnessOwnerId',
            'street',
            'city',
            'state',
            'country',
            'zip',
            'yearsAtAddress',
        ));
        $businessOwnerAddress->setOptionalFields(array(
            'street2'
        ));
        $this->fixState($businessOwnerAddress);
        $request = new Request(array(
            'method' => Request::POST,
            'uri' => $this->prepareURI('/businessowners/' . $businessOwnerAddress->businnessOwnerId . '/previousaddresses'),
            'body' => $businessOwnerAddress
        ));
        $response = $this->client->processRequest($request);

        return new MerchantAccountBusinessOwnerAddress($response);
    }

    /**
     * Add a Business Owner Identity Document
     *
     * @param MerchantAccountBusinessOwnerIdentityDocument $businessOwnerID
     * @return MerchantAccountBusinessOwnerIdentityDocument
     * @throws PaysafeException
     */
    function addBusinessOwnerIdentityDocument(MerchantAccountBusinessOwnerIdentityDocument $businessOwnerID)
    {
        $businessOwnerID->setRequiredFields(array(
            'businnessOwnerId',
            'number',
            'province'
        ));
        $businessOwnerID->setOptionalFields(array(
            'issueDate',
            'expiryDate'
        ));
        $request = new Request(array(
            'method' => Request::POST,
            'uri' => $this->prepareURI('/businessowners/' . $businessOwnerID->businnessOwnerId . '/canadiandrivinglicenses'),
            'body' => $businessOwnerID
        ));
        $response = $this->client->processRequest($request);

        return new MerchantAccountBusinessOwnerIdentityDocument($response);
    }

    /**
     * Add Merchant Eft Bank Account
     *
     * @param MerchantEftBankAccount $bankAccount
     * @return MerchantEftBankAccount
     * @throws PaysafeException
     */
    function addSubMerchantEftBankAccount(MerchantEftBankAccount $bankAccount)
    {
        $bankAccount->setRequiredFields(array(
            'accountNumber',
            'transitNumber',
            'institutionId'
        ));
        $request = new Request(array(
            'method' => Request::POST,
            'uri' => $this->prepareURI('/merchants/' . $bankAccount->merchantId . '/eftbankaccounts'),
            'body' => $bankAccount
        ));
        $response = $this->client->processRequest($request);

        return new MerchantEftBankAccount($response);
    }

    /**
     * Add Merchant Ach Bank Account
     *
     * @param MerchantAchBankAccount $bankAccount
     * @return MerchantAchBankAccount
     * @throws PaysafeException
     */
    function addSubMerchantAchBankAccount(MerchantAchBankAccount $bankAccount)
    {
        $bankAccount->setRequiredFields(array(
            'accountNumber',
            'routingNumber'
        ));
        $request = new Request(array(
            'method' => Request::POST,
            'uri' => $this->prepareURI('/merchants/' . $bankAccount->merchantId . '/achbankaccounts'),
            'body' => $bankAccount
        ));
        $response = $this->client->processRequest($request);

        return new MerchantAchBankAccount($response);
    }

    /**
     * Update Ach Bank Account
     *
     * @param MerchantAchBankAccount $bankAccount
     * @return MerchantAchBankAccount
     * @throws PaysafeException
     */
    function updateMerchantAchBankAccount(MerchantAchBankAccount $bankAccount)
    {
        $bankAccount->setRequiredFields(array(
            'accountNumber',
            'routingNumber'
        ));
        $request = new Request(array(
            'method' => Request::PUT,
            'uri' => $this->prepareURI('/achbankaccounts/' . $bankAccount->id),
            'body' => $bankAccount
        ));
        $response = $this->client->processRequest($request);

        return new MerchantAchBankAccount($response);
    }

    /**
     * Add Sub Merchant Eft Bank Account
     *
     * @param MerchantEftBankAccount $bankAccount
     * @return MerchantEftBankAccount
     * @throws PaysafeException
     */
    function addMerchantEftBankAccount(MerchantEftBankAccount $bankAccount)
    {
        $bankAccount->setRequiredFields(array(
            'accountNumber',
            'transitNumber',
            'institutionId'
        ));
        $request = new Request(array(
            'method' => Request::POST,
            'uri' => $this->prepareURI('/accounts/' . $this->client->getAccount() . '/eftbankaccounts'),
            'body' => $bankAccount
        ));
        $response = $this->client->processRequest($request);

        return new MerchantEftBankAccount($response);
    }

    /**
     * Update Eft Bank Account
     *
     * @param MerchantEftBankAccount $bankAccount
     * @return MerchantEftBankAccount
     * @throws PaysafeException
     */
    function updateMerchantEftBankAccount(MerchantEftBankAccount $bankAccount)
    {
        $bankAccount->setRequiredFields(array(
            'accountNumber',
            'transitNumber',
            'institutionId'
        ));
        $request = new Request(array(
            'method' => Request::PUT,
            'uri' => $this->prepareURI('/eftbankaccounts/' . $bankAccount->id ),
            'body' => $bankAccount
        ));
        $response = $this->client->processRequest($request);

        return new MerchantEftBankAccount($response);
    }

    /**
     * Get Our Terms and Conditions
     *
     * @return TermsAndConditions
     * @throws PaysafeException
     */
    function getTermsAndConditions()
    {
        $request = new Request(array(
            'method' => Request::GET,
            'uri' => $this->prepareURI('/accounts/' . $this->client->getAccount() . '/termsandconditions'),
        ));
        /** @var Response $response */
        $response = $this->client->processRequest($request, true);
        $version = isset($response->headers['X_TERMS_VERSION']) ? $response->headers['X_TERMS_VERSION'] : '1.0';

        return new TermsAndConditions(['version' => $version, 'content' => $response->content]);
    }

    /**
     * Accept Our Terms and Conditions
     *
     * @param TermsAndConditions $termsAndConditions
     * @return TermsAndConditions
     * @throws PaysafeException
     */
    function acceptTermsAndConditions(TermsAndConditions $termsAndConditions)
    {
        $termsAndConditions->setRequiredFields(array(
            'version'
        ));
        $request = new Request(array(
            'method' => Request::POST,
            'uri' => $this->prepareURI('/accounts/' . $this->client->getAccount() . '/termsandconditions'),
            'body' => $termsAndConditions
        ));
        $response = $this->client->processRequest($request);

        return new TermsAndConditions($response);
    }

    /**
     * Activate Merchant Account
     *
     * @param MerchantAccount $merchantAccount
     * @return MerchantAccount
     * @throws PaysafeException
     */
    function activateMerchantAccount(MerchantAccount $merchantAccount)
    {
        $request = new Request(array(
            'method' => Request::POST,
            'uri' => $this->prepareURI('/accounts/' . $this->client->getAccount() . '/activation'),
            'body' => $merchantAccount
        ));
        $response = $this->client->processRequest($request);

        return new MerchantAccount($response);
    }

    /**
     * Create Merchant SubAccount
     *
     * @param MerchantSubAccount $subAccount
     * @return MerchantSubAccount
     * @throws PaysafeException
     */
    function createMerchantSubAccount(MerchantSubAccount $subAccount)
    {
        $subAccount->setRequiredFields(array(
            'name'
        ));
        $subAccount->setOptionalFields(array(
            'eftId',
            'achId'
        ));
        $request = new Request(array(
            'method' => Request::POST,
            'uri' => $this->prepareURI('/accounts/' . $this->client->getAccount() . '/subaccounts'),
            'body' => $subAccount
        ));
        $response = $this->client->processRequest($request);

        return new MerchantSubAccount($response);
    }

    /**
     * Create merchant
     * @param Merchant $merchant
     * @return Merchant
     * @throws PaysafeException
     */
    function createMerchant(Merchant $merchant)
    {
        $merchant->setRequiredFields(array(
            'name'
        ));
        $request = new Request(array(
            'method' => Request::POST,
            'uri' => $this->prepareURI('/merchants'),
            'body' => $merchant
        ));
        $response = $this->client->processRequest($request);

        return new Merchant($response);
    }

    /**
     * @param JSONObject $address
     */
    function fixState(JSONObject $address)
    {
        if (isset($address->state)) {
            $address->state = str_ireplace('OR_', 'OR', $address->state);
        }
    }
}
