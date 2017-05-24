<?php
namespace Bfc\Core\Validator;

class EmailValidator extends AbstractValidator
{
    /**
     * Checks if the given value is a valid email address.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        $isValid = true;

        if (!is_string($value) || !$this->validEmail($value)) {
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * Check the syntax of input email address.
     *
     * @param string $email
     *
     * @return bool
     */
    public function validEmail($email)
    {
        $valid = true;

        // Control characters are not allowed
        if (preg_match('/[\x00-\x1F\x7F-\xFF]/', $email)) {
            $valid = false;
        }

        // Check email length - min 3 (a@a), max 256
        if (!$this->checkLength($email, 3, 256)) {
            $valid = false;
        }

        // Split it into sections using last instance of "@"
        $intAtSymbol = strrpos($email, '@');
        if ($intAtSymbol === false) {
            // No "@" symbol in email.
            $valid = false;
        }
        $arrEmailAddress[0] = substr($email, 0, $intAtSymbol);
        $arrEmailAddress[1] = substr($email, $intAtSymbol + 1);

        // Count the "@" symbols. Only one is allowed, except where contained in quote marks in the local part.
        // Quickest way to check this is to remove anything in quotes.
        // Remove characters escaped with backslash, and the backslash character.
        $arrTempAddress[0] = preg_replace('/\./', '', $arrEmailAddress[0]);
        $arrTempAddress[0] = preg_replace('/"[^"]+"/', '', $arrTempAddress[0]);
        $arrTempAddress[1] = $arrEmailAddress[1];
        $strTempAddress = $arrTempAddress[0] . $arrTempAddress[1];
        // Then check - should be no "@" symbols.
        if (strrpos($strTempAddress, '@') !== false) {
            // "@" symbol found
            $valid = false;
        }

        // Check local portion
        if (!$this->checkLocal($arrEmailAddress[0])) {
            $valid = false;
        }

        // Check domain portion
        if (!$this->checkDomain($arrEmailAddress[1])) {
            $valid = false;
        }

        return $valid;
    }

    /**
     * Checks email section before "@" symbol.
     *
     * @param string $local
     *
     * @return bool
     */
    protected function checkLocal($local) {
        // Local portion can only be from 1 to 64 characters, inclusive.
        // Please note that servers are encouraged to accept longer local parts than 64 characters.
        if (!$this->checkLength($local, 1, 64)) {

            return false;
        }

        // Local portion must be:
        // 1) a dot-atom (strings separated by periods)
        // 2) a quoted string
        // 3) an obsolete format string (combination of the above)
        $arrLocalPortion = explode('.', $local);
        for ($i = 0, $max = sizeof($arrLocalPortion); $i < $max; $i++) {
            if (!preg_match('.^('
                . '([A-Za-z0-9!#$%&\'*+/=?^_`{|}~-][A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]{0,63})|("[^\\\"]{0,62}")'
                . ')$.',
                $arrLocalPortion[$i])) {

                return false;
            }
        }

        return true;
    }

    /**
     * Checks email section after "@" symbol.
     * 
     * @param string $domain
     * 
     * @return bool
     */
    protected function checkDomain($domain) {
        // Total domain can only be from 1 to 255 characters, inclusive
        if (!$this->checkLength($domain, 1, 255)) {
            return false;
        }
        // Check if domain is IP, possibly enclosed in square brackets.
        if (preg_match('/^(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])'
                . '(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])){3}$/',
                $domain) ||
            preg_match('/^\[(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])'
                . '(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])){3}\]$/',
                $domain)) {

            return true;
        } else {
            $arrDomain = explode('.', $domain);
            if (sizeof($arrDomain) < 2) {
                // Not enough parts to domain
                return false;
            }
            for ($i = 0, $max = sizeof($arrDomain); $i < $max; $i++) {
                // Each portion must be between 1 and 63 characters, inclusive
                if (!$this->checkLength($arrDomain[$i], 1, 63)) {

                    return false;
                }
                if (!preg_match('/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|'
                    . '([A-Za-z0-9]+))$/', $arrDomain[$i])) {

                    return false;
                }
                if ($i == $max - 1) {
                    // TLD cannot be only numbers
                    if (strlen(preg_replace('/[0-9]/', '', $arrDomain[$i])) <= 0) {

                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Check given text length is between defined bounds.
     * 
     * @param string $string
     * @param int $minimum
     * @param int $maximum
     *
     * @return bool
     */
    protected function checkLength($string, $minimum, $maximum) {
        $length = strlen($string);
        if (($length < $minimum) || ($length > $maximum)) {
            return false;
        } else {
            return true;
        }
    }
}