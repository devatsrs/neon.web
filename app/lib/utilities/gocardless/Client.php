<?php

namespace App\GoCardlessPro;

/**
 * Main GoCardlessPro Client for making API calls
 */
class Client
{

    const CA_CERT_FILENAME = 'cacert.pem';

    /**
    * @var Core\ApiClient Internal reference to Api Client
    */
    private $api_client;

    /**
     * @param array $config
     *     An array of config parameters
     *
     *     @type string $environment
     *     @type string $access_token
     *     @type string $http_client
     */
    public function __construct($config)
    {
        $this->validate_config($config);

        $access_token = $config['access_token'];

        if (isset($config['base_url'])) {
            $endpoint_url = $config['base_url'];
        } else if (isset($config['environment'])) {
            $endpoint_url = $this->getUrlForEnvironment($config['environment']);
        } else {
            throw new \InvalidArgumentException("Please specify an environment");
        }

        if (isset($config['http_client'])) {
            $http_client = $config['http_client'];
        } else {
            $stack = \App\Lib\GuzzleHttp\HandlerStack::create();
            $stack->push(RetryMiddlewareFactory::buildMiddleware());

            $http_client = new \App\Lib\GuzzleHttp\Client(
                [
                'base_uri' => $endpoint_url,
                'headers' => array(
                'GoCardless-Version' => '2015-07-06',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer " . $access_token,
                'GoCardless-Client-Library' => 'gocardless-pro-php',
                'GoCardless-Client-Version' => '3.0.1',
                'User-Agent' => $this->getUserAgent()
                ),
                'http_errors' => false,
                'verify' => false,//$this->getCACertPath(),
                'handler' => $stack
                ]
            );
        }

        $this->api_client = new \App\GoCardlessPro\Core\ApiClient($http_client);
    }

    
    /**
     * Service for interacting with bank details lookups
     * @return \App\GoCardlessPro\Services\BankDetailsLookupsService
     */
    public function bankDetailsLookups()
    {
        if (!isset($this->bank_details_lookups)) {
            $this->bank_details_lookups = new \App\GoCardlessPro\Services\BankDetailsLookupsService($this->api_client);
        }

        return $this->bank_details_lookups;
    }
    
    /**
     * Service for interacting with creditors
     * @return \App\GoCardlessPro\Services\CreditorsService
     */
    public function creditors()
    {
        if (!isset($this->creditors)) {
            $this->creditors = new \App\GoCardlessPro\Services\CreditorsService($this->api_client);
        }

        return $this->creditors;
    }
    
    /**
     * Service for interacting with creditor bank accounts
     * @return \App\GoCardlessPro\Services\CreditorBankAccountsService
     */
    public function creditorBankAccounts()
    {
        if (!isset($this->creditor_bank_accounts)) {
            $this->creditor_bank_accounts = new \App\GoCardlessPro\Services\CreditorBankAccountsService($this->api_client);
        }

        return $this->creditor_bank_accounts;
    }
    
    /**
     * Service for interacting with customers
     * @return \App\GoCardlessPro\Services\CustomersService
     */
    public function customers()
    {
        if (!isset($this->customers)) {
            $this->customers = new \App\GoCardlessPro\Services\CustomersService($this->api_client);
        }

        return $this->customers;
    }
    
    /**
     * Service for interacting with customer bank accounts
     * @return \App\GoCardlessPro\Services\CustomerBankAccountsService
     */
    public function customerBankAccounts()
    {
        if (!isset($this->customer_bank_accounts)) {
            $this->customer_bank_accounts = new \App\GoCardlessPro\Services\CustomerBankAccountsService($this->api_client);
        }

        return $this->customer_bank_accounts;
    }
    
    /**
     * Service for interacting with customer notifications
     * @return \App\GoCardlessPro\Services\CustomerNotificationsService
     */
    public function customerNotifications()
    {
        if (!isset($this->customer_notifications)) {
            $this->customer_notifications = new \App\GoCardlessPro\Services\CustomerNotificationsService($this->api_client);
        }

        return $this->customer_notifications;
    }
    
    /**
     * Service for interacting with events
     * @return \App\GoCardlessPro\Services\EventsService
     */
    public function events()
    {
        if (!isset($this->events)) {
            $this->events = new \App\GoCardlessPro\Services\EventsService($this->api_client);
        }

        return $this->events;
    }
    
    /**
     * Service for interacting with mandates
     * @return \App\GoCardlessPro\Services\MandatesService
     */
    public function mandates()
    {
        if (!isset($this->mandates)) {
            $this->mandates = new \App\GoCardlessPro\Services\MandatesService($this->api_client);
        }

        return $this->mandates;
    }
    
    /**
     * Service for interacting with mandate imports
     * @return \App\GoCardlessPro\Services\MandateImportsService
     */
    public function mandateImports()
    {
        if (!isset($this->mandate_imports)) {
            $this->mandate_imports = new \App\GoCardlessPro\Services\MandateImportsService($this->api_client);
        }

        return $this->mandate_imports;
    }
    
    /**
     * Service for interacting with mandate import entries
     * @return \App\GoCardlessPro\Services\MandateImportEntriesService
     */
    public function mandateImportEntries()
    {
        if (!isset($this->mandate_import_entries)) {
            $this->mandate_import_entries = new \App\GoCardlessPro\Services\MandateImportEntriesService($this->api_client);
        }

        return $this->mandate_import_entries;
    }
    
    /**
     * Service for interacting with mandate pdfs
     * @return \App\GoCardlessPro\Services\MandatePdfsService
     */
    public function mandatePdfs()
    {
        if (!isset($this->mandate_pdfs)) {
            $this->mandate_pdfs = new \App\GoCardlessPro\Services\MandatePdfsService($this->api_client);
        }

        return $this->mandate_pdfs;
    }
    
    /**
     * Service for interacting with payments
     * @return \App\GoCardlessPro\Services\PaymentsService
     */
    public function payments()
    {
        if (!isset($this->payments)) {
            $this->payments = new \App\GoCardlessPro\Services\PaymentsService($this->api_client);
        }

        return $this->payments;
    }
    
    /**
     * Service for interacting with payouts
     * @return \App\GoCardlessPro\Services\PayoutsService
     */
    public function payouts()
    {
        if (!isset($this->payouts)) {
            $this->payouts = new \App\GoCardlessPro\Services\PayoutsService($this->api_client);
        }

        return $this->payouts;
    }
    
    /**
     * Service for interacting with payout items
     * @return \App\GoCardlessPro\Services\PayoutItemsService
     */
    public function payoutItems()
    {
        if (!isset($this->payout_items)) {
            $this->payout_items = new \App\GoCardlessPro\Services\PayoutItemsService($this->api_client);
        }

        return $this->payout_items;
    }
    
    /**
     * Service for interacting with redirect flows
     * @return \App\GoCardlessPro\Services\RedirectFlowsService
     */
    public function redirectFlows()
    {
        if (!isset($this->redirect_flows)) {
            $this->redirect_flows = new \App\GoCardlessPro\Services\RedirectFlowsService($this->api_client);
        }

        return $this->redirect_flows;
    }
    
    /**
     * Service for interacting with refunds
     * @return \App\GoCardlessPro\Services\RefundsService
     */
    public function refunds()
    {
        if (!isset($this->refunds)) {
            $this->refunds = new \App\GoCardlessPro\Services\RefundsService($this->api_client);
        }

        return $this->refunds;
    }
    
    /**
     * Service for interacting with subscriptions
     * @return \App\GoCardlessPro\Services\SubscriptionsService
     */
    public function subscriptions()
    {
        if (!isset($this->subscriptions)) {
            $this->subscriptions = new \App\GoCardlessPro\Services\SubscriptionsService($this->api_client);
        }

        return $this->subscriptions;
    }
    
    private function getUrlForEnvironment($environment)
    {
        $environment_urls = array(
            "live" => "https://api.gocardless.com/",
            "sandbox" => "https://api-sandbox.gocardless.com/"
        );

        if(!array_key_exists($environment, $environment_urls)) {
            throw new \InvalidArgumentException("$environment is not a valid environment, please use one of " . implode(array_keys($environment_urls), ", "));
        }

        return $environment_urls[$environment];
    }

    /**
     * Ensures a config is valid and sets defaults where required
     *
     * @param array[string]mixed $config the client configuration options
     */
    private function validate_config($config)
    {
        $required_option_keys = array('access_token', 'environment');

        foreach ($required_option_keys as $required_option_key) {
            if (!isset($config[$required_option_key])) {
                throw new \Exception('Missing required option `' . $required_option_key . '`.');
            }

            if (!is_string($config[$required_option_key])) {
                throw new \Exception('Option `'. $required_option_key .'` can only be a string.');
            }
        }
    }

    /**
     * Gets the client's user agent for API calls
     *
     * @return string
     */
    private function getUserAgent()
    {
        $curlinfo = curl_version();
        $uagent = array();
        $uagent[] = 'gocardless-pro-php/3.0.1';
        $uagent[] = 'schema-version/2015-07-06';
        $uagent[] = 'GuzzleHttp/' . \App\Lib\GuzzleHttp\Client::VERSION;
        $uagent[] = 'php/' . phpversion();
        if (extension_loaded('curl') && function_exists('curl_version')) {
            $uagent[] = 'curl/' . \curl_version()['version'];
            $uagent[] = 'curl/' . \curl_version()['host'];
        }
        return implode(' ', $uagent);
    }

    /**
     * Internal function for finding the path to cacert.pem
     * @return Path to the cacert.pem file
     */
    private function getCACertPath()
    {
        return dirname(__FILE__) . "/../" . self::CA_CERT_FILENAME;
    }
}
