<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Credit Card Validation Helpers
 *
 * Adopted from the Vork MVC framework to work as a CodeIgniter Helper
 *
 * @package		Order
 * @subpackage	Helpers
 * @author		Vork Development Team
 *              Geoff Doty <n2geoff@gmail.com>
 * @link		http://www.vork.us/
 */

// ------------------------------------------------------------------------

/**
 * Credit card types; keys mapped to the AuthorizeNet card type response
 *
 * @var array
 */
if ( ! function_exists('get_card_types'))
{
    function get_card_types()
    {
        $card_types = array(
                            4 => 'Visa', 
                            5 => 'MasterCard', 
                            3 => 'American Express', 
                            6 => 'Discover Card',
                            8 => 'Diners Club', 
                            1 => 'JCB (Japanese Credit Bureau)', 
                            7 => 'Australian BankCard',
                            2 => 'enRoute'
                    );
    
        return $card_types;
    }                     
}

/**
 * Gets a Credit Card for a particular card type key
 *
 * @see get_card_types()
 * @var array
 */
if ( ! function_exists('get_card_type'))
{
    function get_card_type($card_type)
    {
        $card_types = get_card_types();
        
        foreach($card_types as $card)
        {
            if(in_array($card_type, $card_types))
            {
                return $card;
            }
        }
        
        return FALSE;
    }
}
/**
 * Validates the length of credit card number is correct for the card type
 *
 * @param int $cc
 * @param int $card_type This must match the key from card_types array
 * @return boolean
 */
if ( ! function_exists('is_valid_length'))
{
    function is_valid_length($cc, $card_type) 
    {
        $length[16] = array(1, 4, 5, 6, 7); //JCB, Visa, MasterCard, Discover Card, Australian BankCard
        $length[15] = array(1, 2, 3); //JCB, enRoute, American Express
        $length[14] = array(8); //Diners Club
        $length[13] = array(4); //Visa
        $card_length = strlen($cc);
        
        return (isset($length[$card_length]) && in_array($card_type, $length[$card_length]));
    }
}

/**
 * Determines credit card type by the credit card number
 *
 * @param int $cc Can be the first four digits of the credit card number, the entire number or anything in between
 * @return int Matches the keys in card_types
 */
if ( ! function_exists('get_card_type_by_prefix'))
{
    function get_card_type_by_prefix($cc) 
    {
        $card_type = 0;
        $prefix = substr($cc, 0, 4);
        
        if ($prefix >= 4000 && $prefix <= 4999) 
        {
           $card_type = 4; //Visa
        } 
        else if ($prefix >= 5100 && $prefix <= 5599) 
        {
           $card_type = 5; //MasterCard
        } 
        else if (($prefix >= 3400 && $prefix <= 3499) || ($prefix >= 3700 && $prefix <= 3799)) 
        {
           $card_type = 3; //American Express
        } 
        else if (($prefix >= 3000 && $prefix <= 3059) || ($prefix >= 3600 && $prefix <= 3699)
                                                      || ($prefix >= 3800 && $prefix <= 3899)) 
        {
           $card_type = 8; //Diners Club
           $is_carte_blanch = ($prefix >= 3890 && $prefix <= 3899); //not used
        } 
        else if ($prefix >= 3528 && $prefix <= 3589) 
        {
           $card_type = 1; //JCB
        } 
        else 
        {
            switch ($prefix) 
            {
                case 6011:
                    $card_type = 6; //Discover Card
                    break;
                case 1800:
                case 2131:
                    $card_type = 1; //JCB
                    break;
                case 2014:
                case 2149:
                    $card_type = 2; //enRoute
                    break;
                case 5610:
                    $card_type = 7; //Australian BankCard
                    break;
            }
        }
        return get_card_type($card_type);
    }
}

/**
 * Verifies if a credit card number passes the Mod 10 specification
 *
 * @param int $cc
 * @return bool
 */
 if ( ! function_exists('is_mod10_valid'))
{
    function is_mod10_valid($cc) 
    {
         /*
         * Every second digit in the Mod 10 (Luhn) algorithm gets doubled 
         * and then the resulting digit(s) are added together.
         *
         * This array maps the orignal number to the digit after the Mod 10 equation
         */
        $mod10_digits = array(0 => 0, 1 => 2, 2 => 4, 3 => 6, 4 => 8, 5 => 1, 6 => 3, 7 => 5, 8 => 7, 9 => 9);
    
        $card_length = strlen($cc);
        if ($card_length > 16 || $card_length < 13 || !is_numeric($cc)) 
        {
            return false;
        }
        
        $digit_sum = 0;
        $current_bit = 1;
        $start_bit = ($card_length % 2);
        	for ($x = 0; $x < $card_length; $x++) 
            {
            	$current_bit =! $current_bit;
            	if ($current_bit == $start_bit) 
                {
            	    $cc[$x] = $mod10_digits[$cc[$x]];
            	}
            	$digit_sum += $cc[$x];
        	}
        return (boolean) !($digit_sum % 10);
    }
}