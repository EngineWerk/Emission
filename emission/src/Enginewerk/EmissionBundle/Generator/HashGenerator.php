<?php
namespace Enginewerk\EmissionBundle\Generator;

class HashGenerator
{
    private static $feed = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

    /**
     * @param string|null $sequence
     * @param int|null $characters
     *
     * @return string
     */
    public static function generate($sequence = null, $characters = null)
    {
        return self::generateSequencedHash($sequence, $characters);
    }

    /**
     * Generate random HashGenerator at specified length based on "feed".
     *
     * @param  int       $length
     * @param  string    $charFeed
     *
     * @throws InvalidLengthException
     *
     * @return string
     */
    public static function generateRandomHash($length = 4, $charFeed = null)
    {
        $charFeed = $charFeed ?: self::$feed;

        if ($length <= 0) {
            throw new InvalidLengthException('Length lower than, or equal 0');
        }

        $hash = '';
        $feedMaxIndex = strlen($charFeed) - 1;

        for ($i = 1; $i <= $length; ++$i) {
            $hash .= $charFeed[mt_rand(0, $feedMaxIndex)];
        }

        return $hash;
    }

    /**
     * Returns "next" hash, based on $sequence
     * B afer A, C afer B, ABC after ABB.
     *
     * @param  string $sequence
     * @param  string $characters
     *
     * @return string
     */
    public static function generateSequencedHash($sequence = null, $characters = null)
    {
        if ($characters === null) {
            $characters = self::$feed;
        }

        if ($sequence === null || $sequence === '') {
            return $characters[0];
        }

        $sidChars = str_split($sequence);

        //To change oldest character
        $sidChars = array_reverse($sidChars);

        foreach ($sidChars as $position => $sidChar) {
            $newValue = self::getNextFeedValue($sidChar, $characters);

            if ($newValue === null) {
                $sidChars[$position] = $characters[0];

                if (!isset($sidChars[$position + 1])) {
                    $sidChars[$position + 1] = $characters[0];
                }
            } else {
                $sidChars[$position] = $newValue;

                $sidChars = array_reverse($sidChars);

                return implode('', $sidChars);
            }
        }

        $sidChars = array_reverse($sidChars);

        return implode('', $sidChars);
    }

    /**
     * @param  string $currentValue
     * @param  string $characters
     *
     * @return string|null
     */
    private static function getNextFeedValue($currentValue, $characters)
    {
        $currentPosition = strpos($characters, $currentValue);

        if ($currentPosition === (strlen($characters) - 1)) {
            return null;
        } else {
            return $characters[$currentPosition + 1];
        }
    }
}
