<?php

namespace App\Core;

use App\Core\MyPDO;

class Validation
{
    const DEFAULT_VALIDATION_ERRORS = [
        'required' => 'Please enter the %s',
        'accepted' => 'To be able to register You must accept out terms and conditions',
        'email' => 'The %s is not a valid email address',
        'min' => 'The %s must have at least %s characters',
        'max' => 'The %s must have at most %s characters',
        'between' => 'The %s must have between %d and %d characters',
        'same' => 'The %s must match with %s',
        'alphanumeric' => 'The %s should have only letters and numbers',
        'secure' => 'The %s must have between 8 and 64 characters and contain at least one number, one upper case letter, one lower case letter and one special character',
        'unique' => 'The %s already exists',
        'file' => 'The chosen %s is not valid file. Missing extention?',
        'max_size' => 'The %s file size must be less than %s.',
        'mimes' => 'The chosen %s file type is not valid. Allowed file types are: %s.',
    ];


    /**
     * Validate
     * @param array $data
     * @param array $fields
     * @param array $messages
     * @return array
     */
    public static function validate(array $data, array $fields, array $messages = []): array
    {
        // Split the array by a separator, trim each element
        // and return the array
        $split = fn ($str, $separator) => array_map('trim', explode($separator, $str));

        // get the message rules
        $rule_messages = array_filter($messages, fn ($message) => is_string($message));

        // overwrite the default message
        $validation_errors = array_merge(self::DEFAULT_VALIDATION_ERRORS, $rule_messages);

        $errors = [];

        foreach ($fields as $field => $option) {

            $rules = $split($option, '|');
            foreach ($rules as $rule) {
                // get rule name params
                $params = [];

                // if the rule has parameters e.g., min: 1
                if (strpos($rule, ':')) {
                    [$rule_name, $param_str] = $split($rule, ':');
                    $params = $split($param_str, ',');
                } else {
                    $rule_name = trim($rule);
                }

                // by convention, the callback should be is_<rule> e.g.,is_required
                $fn = 'is_' . $rule_name;

                if (method_exists(__CLASS__, $fn)) {

                    $pass = call_user_func_array([__CLASS__, $fn], [$data, $field, ...$params]);

                    // print_r($fn . ': ' . $pass . PHP_EOL); //uncoment to debug

                    if (!$pass) {
                        // get the error message for a specific field and rule if exists
                        // otherwise get the error message from the $validation_errors
                        $error_message = $messages[$field][$rule_name] ?? $validation_errors[$rule_name];

                        // Check if the field already has errors
                        if (!isset($errors[$field])) {
                            // If not, initialize an array to hold all the error messages for the field
                            $errors[$field] = [];
                        }

                        // in error messages first specifier is for the field name, all others are for params
                        // check if number of specifiers is smaller than number of params.
                        if (substr_count($error_message, '%s') < count($params)) {
                            // if smaller there are not enough specifiers for all parameters
                            // so we implode $params array to be a string and shown on last specifier
                            // (example: needed to show all mime types given by rule. see mimes rule with it's error message)
                            $errors[$field][] = sprintf(
                                $error_message,
                                $field,
                                implode(', ', $params)
                            );
                        } else {
                            // if not, there is sufficient number of specifiers
                            // to show all params so we spred $params array
                            $errors[$field][] = sprintf(
                                $error_message,
                                $field,
                                ...$params
                            );
                        }
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Return true if a string is not empty
     * @param array $data
     * @param string $field
     * @return bool
     */
    protected static function is_required(array $data, string $field): bool
    {
        return isset($data[$field]) && trim($data[$field]) !== '';
    }

    /**
     * Return true if a string is not empty
     * @param array $data
     * @param string $field
     * @return bool
     */
    protected static function is_accepted(array $data, string $field): bool
    {
        return isset($data[$field]) && trim($data[$field]) !== '';
    }

    /**
     * Return true if the value is a valid email
     * @param array $data
     * @param string $field
     * @return bool
     */
    protected static function is_email(array $data, string $field): bool
    {
        if (empty($data[$field])) {
            return true;
        }

        return filter_var($data[$field], FILTER_VALIDATE_EMAIL);
    }

    /**
     * Return true if a string has at least min length
     * @param array $data
     * @param string $field
     * @param int $min
     * @return bool
     */
    protected static function is_min(array $data, string $field, int $min): bool
    {
        if (!isset($data[$field])) {
            return true;
        }

        return mb_strlen($data[$field]) >= $min;
    }

    /**
     * Return true if a string cannot exceed max length
     * @param array $data
     * @param string $field
     * @param int $max
     * @return bool
     */
    protected static function is_max(array $data, string $field, int $max): bool
    {
        if (!isset($data[$field])) {
            return true;
        }

        return mb_strlen($data[$field]) <= $max;
    }

    /**
     * @param array $data
     * @param string $field
     * @param int $min
     * @param int $max
     * @return bool
     */
    protected static function is_between(array $data, string $field, int $min, int $max): bool
    {
        if (!isset($data[$field])) {
            return true;
        }

        $len = mb_strlen($data[$field]);
        return $len >= $min && $len <= $max;
    }

    /**
     * Return true if a string equals the other
     * @param array $data
     * @param string $field
     * @param string $other
     * @return bool
     */
    protected static function is_same(array $data, string $field, string $other): bool
    {
        if (isset($data[$field], $data[$other])) {
            return $data[$field] === $data[$other];
        }

        if (!isset($data[$field]) && !isset($data[$other])) {
            return true;
        }

        return false;
    }

    /**
     * Return true if a string is alphanumeric
     * @param array $data
     * @param string $field
     * @return bool
     */
    protected static function is_alphanumeric(array $data, string $field): bool
    {
        if (!isset($data[$field])) {
            return true;
        }

        return ctype_alnum($data[$field]);
    }

    /**
     * Return true if a password is secure
     * @param array $data
     * @param string $field
     * @return bool
     */
    protected static function is_secure(array $data, string $field): bool
    {
        if (!isset($data[$field])) {
            return false;
        }

        $pattern = "#.*^(?=.{8,64})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#";
        return preg_match($pattern, $data[$field]);
    }

    /**
     * Return true if the $value is unique in the column of a table
     * @param array $data
     * @param string $field
     * @param string $table
     * @param string $column
     * @return bool
     */
    protected static function is_unique(array $data, string $field, string $table, string $column): bool
    {
        if (!isset($data[$field])) {
            return true;
        }

        $sql = "SELECT `$column` FROM $table WHERE `$column` = :value";

        $con = new MyPDO();

        $stmt = $con->run($sql, ["value" => $data[$field]])->fetchColumn();

        return $stmt === false;
    }

    /**
     * Check if file has an extension. Files with extension are valid file
     *
     * @param array $data The data array to check
     * @param string $field The field name to check in the data array
     * @return bool
     */
    protected static function is_file(array $data, string $field): bool
    {
        if (!isset($data[$field])) {
            return true;
        }
        // check if file has extention
        $ext = pathinfo($data[$field]['name'], PATHINFO_EXTENSION);

        return $ext;
    }

    /**
     * Check if the size of the specified field in the data array is within the maximum size limit.
     *
     * @param array $data The input data array
     * @param string $field The field to check in the data array
     * @param int $max_size The maximum size limit to compare against
     * @return bool
     */
    protected static function is_max_size(array $data, string $field, string $max_size): bool
    {
        if (!isset($data[$field])) {
            return true;
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $max_size_units = strtoupper(preg_replace('/[0-9]+/', '', $max_size));
        $factor = array_search($max_size_units, $units);

        return $data[$field]['size'] <= (int)$max_size * pow(1024, $factor);
    }

    /**
     * Check if the given field's file type is in the specified list of MIME types.
     *
     * @param array $data The data array to check
     * @param string $field The field name to check
     * @param string ...$mimes The list of MIME types to check against
     * @return bool
     */
    protected static function is_mimes(array $data, string $field, string ...$mimes): bool
    {
        if (!isset($data[$field])) {
            return true;
        }

        return in_array(pathinfo($data[$field]['name'], PATHINFO_EXTENSION), $mimes);
    }
}
