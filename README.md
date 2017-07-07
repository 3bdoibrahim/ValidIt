# Getting started
ValidIt is standalone PHP data validation class.

## Installation
1. Download ValidIt
2. Unzip it and copy the files into your PHP project directory.

Include it in your project:

```php
require "validit.class.php";
$validator = new ValidIt();
$rules = array(
	'username' => 'required|alpha_numeric',
	'password' => 'required|min_len:5|max_len:50'
);
$is_valid = $validator->is_valid($_POST, $rules);

if($is_valid === true) {
	// continue
} else {
	print_r($validator->get_errors());
}
```


#### Available Methods

```php
// Shorthand validation
is_valid(array $data, array $rules)

// Return an array of validation errors
get_errors();
```

# Example (Long format)

The following example is part of a registration form, the flow should be pretty standard

```php
require "validit.class.php";

$validit = new ValidIt();

$rules = array(
    'username' => 'required|alpha_numeric|min_len:5|max_len:50',
    'password' => 'required|min_len:5|max_len:50',
    'email' => 'required|email',
    'gender' => 'required|exact_len:1|contains:{m,f}',
    'credit_card' => 'required|cc'
);

$is_valid = $validit->is_valid($_POST, $rules);

if ($is_valid === true) {
    // continue
} else {
    print_r($validit->get_errors());
}
```
Available Validators
--------------------
* required `Determine if the provided value is present and not empty.`
* max_len:n `Determine if the provided value length is less or equal to a specific value. n = length parameter.`
* min_len:n `Determine if the provided value length is more or equal to a specific value. n = length parameter.`
* exact_len:n `Determine if the provided value length is equal to a specific value. n = length parameter.`
* in_range:{n,m} `Determine if the provided value is a number and in a specific range. n = start, m = end. examples: in_range:{1,10} = > 1 & < 10, in_range:{1,} = > 1, in_range:{,10} = < 10`
* contains:{a,b,c} `Determine if the provided value is contains at least one of specific values.`
* contains_list:{a,b,c} `Determine if the provided value is equal to one of specific values.`
* doesnt_contain_list:{a,b,c} `Determine if the provided value is not equal to any of specific values.`
* starts:a `Determine if the provided value is starts with a specific value.`
* alpha `Determine if the provided value is contains only letters. (a-z, A-Z)`
* alpha_space `Determine if the provided value is contains only letters and spaces. (a-z, A-Z, 0-9, \s)`
* alpha_numeric `Determine if the provided value is contains only letters and numbers. (a-z, A-Z, 0-9)`
* alpha_numeric_space `Determine if the provided value is contains only letters, numbers and spaces. (a-z, A-Z, 0-9, \s)`
* alpha_dash `Determine if the provided value is contains only letters, dashes and underscores. (a-z, A-Z, 0-9, _-)`
* numeric `Determine if the provided value is a number.`
* integer `Determine if the provided value is a integer.`
* boolean `Determine if the provided value is boolean, returns TRUE for "1", "true", "on" and "yes"`
* float `Determine if the provided value is float`
* email `Determine if the provided value is a valid email address`
* url `Determine if the provided value is a valid URL or subdomain`
* url_exists `Determine if the url exists and is accessible`
* ip `Determine if the provided value is a valid generic IP address`
* ipv4 `Determine if the provided value is a valid IPv4 address`
* ipv6 `Determine if the provided value is a valid IPv6 address`
* guidev4 `Determine if the provided value is a valid GUIDv4 (Globally Unique Identifier)`
* cc `Determine if the provided value is a valid credit card number (Visa, MasterCard, American Express, Diners Club, Discover, JCB)`
* name `Determine if the provided value is a valid format human name`
* street_address `Determine if the provided value is a valid street address. 1 number, 1 or more space, 1 or more letters`
* date `Determine if the provided input is a valid date (ISO 8601)`
* iban `Determine if the provided input is a valid IBAN`
* phone_number `Determine if the provided input is a valid phone numbers that match the following examples: 555-555-5555, 5555425555, 555 555 5555, 1(519) 555-4444, 1 (519) 555-4422, 1-555-555-5555`
* regex `Custom regex (regular expressions) validator using the following format: 'regex:{/your regex/}'`
* json_string `Determine if the provided value is a valid JSON string.`

### Enjoy
