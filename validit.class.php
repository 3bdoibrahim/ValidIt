<?php

/**
 * ValidIt - A fast, standalone PHP input validation class.
 *
 * @author      Khalid Omar
 * @license     GPLv3.0
 *
 * @link        https://github.com/khalidzeiter/ValidIt
 *
 * @version     0.1
 */

require 'errors.en.php';   // Error Messages
Class ValidIt {
    private $errors = array();  // The array of error messages
    private $valid = true;     // Valid or Not condition

    /**
     * Shorthand method for validation.
     *
     * @param array $data The data to be validated
     * @param array $rules The VALIDIT validators
     *
     * @return True(boolean) on success
     *         or the array of error messages on failure
     */
    protected function validate($data, $rules) {
        global $errors;
        foreach ($rules as $input => $rule) {
            if (!isset($data[$input])) {
                $this->{'invalid'}($input);
            }
            $rules = explode('|', $rule);
            foreach ($rules as $rule) {
                $method = null;
                $param = null;

                if (strstr($rule, ':') !== false) {
                    $rule = explode(':', $rule);

                    $method = $rule[0];
                    $param = $rule[1];

                    $this->$method($input, $data[$input], $param);
                } else {
                    $method = $rule;
                    $this->$method($input, $data[$input], $param);
                }
            }
        }
    }

    /* ------------------------- Validators ------------------------------------ */

    /**
     * Determine if the index(field) is valid.
     * @return FALSE
     */
    protected function invalid($field) {
        $this->set_errors($field, null, __FUNCTION__);
        return false;
    }

    /**
     * Determine if the provided value is present and not empty.
     * Usage: 'index' => 'required'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function required($field, $input, $param = null) {
        if (isset($input) && !empty($input)
            || $input === 0 || $input === '0'
        ) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value length is more or equal to a specific value.
     * Usage: 'index' => 'min_len:10'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function min_len($field, $input, $param = null) {
        if (strlen($input) >= $param) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value length is less or equal to a specific value.
     * Usage: 'index' => 'max_len:10'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function max_len($field, $input, $param = null) {
        if (strlen($input) <= $param) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value length is equal to a specific value.
     * Usage: 'index' => 'exact_len:10'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function exact_len($field, $input, $param = null) {
        if (strlen($input) == $param) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is a number and in a specific range.
     * Usage:   'index' => 'in_range:{1,10}'     // > 1 & < 10
     *       or 'index' => 'in_range:{1,}'       // > 1
     *       or 'index' => 'in_range:{,10}'      // < 10
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function in_range($field, $input, $param = null) {
        $str = explode(',', strtolower(trim($param, '{}')));
        if ((count($str) == 2)) {
            if ($str[1] == '') {
                $error = 'min_num'; // {1,:}
                $param = $str[0];
                $state = ($input >= (int)$str[0]) ? true : false;
            } else if ($str[0] == '') {
                $error = 'max_num'; // {:,10}
                $param = $str[1];
                $state = ($input <= (int)$str[1]) ? true : false;
            } else {
                $error = 'in_range'; // {1,10}
                $param = $str[0] . " and " . $str[1];
                $state = ($input >= (int)$str[0] && $input <= (int)$str[1]) ? true : false;
            }
        } else {
            $error = 'not_valid_range';
            $state = false;
        }
        if (ctype_digit($input) && $state == true) {
            return true;
        } else {
            $this->set_errors($field, $param, $error);
            return false;
        }
    }

    /**
     * Determine if the provided value is contains at least one of specific values.
     * Usage:  'index' => 'contains:{a,b,c,d}'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function contains($field, $input, $param = null) {
        $str = explode(',', trim($param, '{}'));
        foreach ($str as $c) {
            if (preg_match("/$c/", $input)) {
                continue;
            } else {
                $this->set_errors($field, $c, __FUNCTION__);
                return false;
            }
        }
        return true;
    }

    /**
     * Determine if the provided value is equal to one of specific values.
     * Usage:  'index' => 'contains_list:{a,b,c,d}'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function contains_list($field, $input, $param = null) {
        $str = explode(',', strtolower(trim($param, '{}')));
        if (in_array($input, $str)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is not equal to any of specific values.
     * Usage:  'index' => 'doesnt_contain_list:{a,b,c,d}'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function doesnt_contain_list($field, $input, $param = null) {
        $str = explode(',', strtolower(trim($param, '{}')));
        if (!in_array($input, $str)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is starts with a specific value.
     * Usage:  'index' => 'starts:a'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function starts($field, $input, $param = null) {
        if (preg_match("/^$param/i", $input)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is contains only letters.
     * Usage: 'index' => 'alpha'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function alpha($field, $input, $param = null) {
        if (ctype_alpha($input)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is contains only letters and spaces.
     * Usage: 'index' => 'alpha_space'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function alpha_space($field, $input, $param = null) {
        if (ctype_alpha($input) || preg_match('/\s/', $input)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is contains only letters and numbers.
     * Usage: 'index' => 'alpha_numeric'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function alpha_numeric($field, $input, $param = null) {
        if (ctype_alnum($input)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is contains only letters, numbers and spaces.
     * Usage: 'index' => 'alpha_numeric_space'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function alpha_numeric_space($field, $input, $param = null) {
        if (ctype_alnum($input) || preg_match('/\s/', $input)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is contains only letters, dashes and underscores.
     * Usage: 'index' => 'alpha_dash'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function alpha_dash($field, $input, $param = null) {
        if (ctype_alpha($input) || preg_match('/_|-/', $input)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is a number.
     * Usage: 'index' => 'numeric'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function numeric($field, $input, $param = null) {
        if (ctype_digit($input)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is an integer.
     * Usage: 'index' => 'integer'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function integer($field, $input, $param = null) {
        if (filter_var($input, FILTER_VALIDATE_INT)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is boolean.
     * Usage: 'index' => 'boolean'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function boolean($field, $input, $param = null) {
        if (filter_var($input, FILTER_VALIDATE_BOOLEAN)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is float.
     * Usage: 'index' => 'float'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function float($field, $input, $param = null) {
        if (filter_var($input, FILTER_VALIDATE_FLOAT)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is a valid Email.
     * Usage: 'index' => 'email'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function email($field, $input, $param = null) {
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is a valid URL.
     * Usage: 'index' => 'url'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function url($field, $input, $param = null) {
        if (filter_var($input, FILTER_VALIDATE_URL)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is exists and accessible URL.
     * Usage: 'index' => 'url_exists'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function url_exists($field, $input, $param = null) {
        $url = parse_url($input);
        if (@checkdnsrr($url['host'])) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is a valid IP address.
     * Usage: 'index' => 'ip'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function ip($field, $input, $param = null) {
        if (filter_var($input, FILTER_VALIDATE_IP)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is a valid IPv4 address.
     * Usage: 'index' => 'ipv4'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function ipv4($field, $input, $param = null) {
        if (filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is a valid IPv6 address.
     * Usage: 'index' => 'ipv6'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function ipv6($field, $input, $param = null) {
        if (filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is a valid GUID (version 4).
     * Usage: 'index' => 'guidv4'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function guidv4($field, $input, $param = null) {
        $pattern = "/\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i";
        if (preg_match($pattern, $input)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is a valid credit card number.
     * {Visa, MasterCard, American Express, Diners Club, Discover, JCB}
     *
     * Usage: 'index' => 'cc'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function cc($field, $input, $param = null) {
        $pattern = '/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|6(?:011|5[0-9]{2})[0-9]{12}(?:2131|1800|35\d{3})\d{11})$/m';
        if (preg_match($pattern, $input)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is a valid Full Name.
     * Usage: 'index' => 'name'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function name($field, $input, $param = null) {
        $pattern = "/^([\ \.\x{00c0}-\x{01ff}\x{0627}-\x{0649}a-zA-Z'\-])+$/iu";
        if (preg_match($pattern, $input)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is a valid street address {letters, numbers, spaces}.
     * Usage: 'index' => 'street_address'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function street_address($field, $input, $param = null) {
        if (preg_match('/[\x{0627}-\x{0649}a-zA-Z]|\d|\s/iu', $input)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is a valid date.
     * Usage: 'index' => 'date'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function date($field, $input, $param = null) {
        $pattern = "#^(((0?[1-9]|1\d|2[0-8])[\/\-\.](0?[1-9]|1[012])|(29|30)[\/\-\.](0?[13456789]|1[012])|31[\/\-\.](0?[13578]|1[02]))[\/\-\.](19|[2-9]\d)\d{2}|29[\/\-\.]0?2[\/\-\.]((19|[2-9]\d)(0[48]|[2468][048]|[13579][26])|(([2468][048]|[3579][26])00)))$#";
        if (preg_match($pattern, $input)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is a valid IBAN.
     * Usage: 'index' => 'iban'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function iban($field, $input, $param = null) {
        $pattern = "/[a-zA-Z]{2}[0-9]{2}[a-zA-Z0-9]{4}[0-9]{7}([a-zA-Z0-9]?){0,16}/";
        if (preg_match($pattern, $input)) {
            return true;
        } else {
            $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is a valid phone number.
     * Usage: 'index' => 'phone_number'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function phone_number($field, $input, $param = null) {
        $pattern = '/^(\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/i';
        if (preg_match($pattern, $input)) {
            return true;
        } else {
            return $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Custom regex (regular expressions) validator.
     * Usage: 'index' => 'regex:{/your regex/}'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function regex($field, $input, $param = null) {
        $regex = trim($param, '{}');
        if (preg_match($regex, $input)) {
            return true;
        } else {
            return $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Determine if the provided value is a valid JSON string.
     * Usage: 'index' => 'json_string'
     *
     * @return TRUE on success or FALSE on failure
     */
    protected function json_string($field, $input, $param = null) {
        if (json_decode($input)) {
            return true;
        } else {
            return $this->set_errors($field, $param, __FUNCTION__);
            return false;
        }
    }

    /**
     * Set the array of error messages.
     */
    protected function set_errors($field, $param = null, $rule = null) {
        global $errors;

        $this->errors[] = str_replace(array('{field}', '{param}'), array($field, $param), $errors[$rule]);
        $this->valid = false;
    }

    /**
     * Get the array of error messages.
     *
     * @return array
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * Get the validation state.
     *
     * @return TRUE on success or FALSE on failure
     */
    public function is_valid($data, $rules) {
        $this->validate($data, $rules);
        return $this->valide;
    }
}