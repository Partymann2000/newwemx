<?php

namespace App\Extensions\Traits;

trait GatewayFoundationHelper
{
    public function getConfig()
    {
        // if property exists called displayName in the extension, return it
        if (method_exists($this, 'setConfig')) {
            return $this->setConfig();
        }

        return [];
    }

    public function getGatewayDescription()
    {
        // if property exists called description in the extension, return it
        if (property_exists($this, 'gatewayDescription')) {
            return $this->gatewayDescription;
        }

        return null;
    }

    public function getGatewayIcon()
    {
        // if property exists called icon in the extension, return it
        if (property_exists($this, 'gatewayIcon')) {
            return $this->gatewayIcon;
        }

        return null;
    }

    public function getSupportedCurrencies(): array
    {
        // if method exists called currencies in the extension, return it
        if (method_exists($this, 'currencies')) {
            return (array) $this->currencies();
        }

        // if property exists called currencies in the extension, return it
        if (property_exists($this, 'currencies')) {
            return (array) $this->currencies;
        }

        return [];
    }

    public function getGatewayType(): string
    {
        // check if $this->$gatewayType is set, if so check if its "subscription"
        if (isset($this->gatewayType) && strtolower($this->gatewayType) === 'subscription') {
            return 'subscription';
        }

        return 'payment';
    }

    public function getBaseCurrency()
    {
        $currencies = $this->getSupportedCurrencies();

        // if array is empty, return base currency
        if (empty($currencies)) {
            return baseCurrency();
        }

        // if the array contains the application's base currency, return it
        if (in_array(baseCurrency(), $currencies)) {
            return baseCurrency();
        }

        // return the first currency in the array
        return $currencies[0];
    }

    public function hasWebhook(): bool
    {
        // if method exists called webhook in the extension, return true
        return method_exists($this, 'webhook');
    }

    public function hasCallback(): bool
    {
        // if method exists called callback in the extension, return true
        return method_exists($this, 'callback');
    }
}
