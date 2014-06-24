<?php

namespace Enginewerk\EmissionBundle\Generator;

/**
 * Description of Hash Generator
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class Hash 
{
    private static $feed = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    
    public static function generate($sequence = null, $length = null)
    {
        return self::generateSequencedHash($sequence);
    }
    
    /**
     * Generate random Hash at specified lenght based on "feed"
     * 
     * @param type $length
     * @param type $charFeed
     * @return type
     * @throws Exception
     */
    public static function genereateRandomHash($length = 4, $charFeed = null)
    {
        if ($length <= 0) {
            throw new \Exception('Length lower than, or equal 0');
        }
        
        $hash = '';
        
        $feedMaxIndex = strlen(self::$feed) - 1;
        
        for ($i=1; $i<=$length; $i++) {
            $hash .= self::$feed[rand(0, $feedMaxIndex)];
        }
        
        return $hash;
    }
    
    /**
     * Returns "next" hash, based on $sequence
     * B afer A, C afer B, ABC after ABB
     * 
     * @param type $lastSID
     * @return type
     */
    public static function generateSequencedHash($sequence = null, $length = null, $characters = null)
    {
        if($characters === null)
            $characters = self::$feed;
        
        if($sequence === null || $sequence == '')
            return $characters[0];
        
        $sidChars = str_split($sequence);
        
        //To change oldest character
        $sidChars = array_reverse($sidChars);
        
        foreach($sidChars as $position => $sidChar)
        {
            $newValue = self::getNextFeedValue($sidChar, $characters);

            if($newValue === null)
            {
                $sidChars[$position] = $characters[0];
                
                if(!isset($sidChars[$position+1]))
                    $sidChars[$position+1] = $characters[0];
            } 
            else 
            {
                $sidChars[$position] = $newValue;
                
                $sidChars = array_reverse($sidChars);
                return implode('', $sidChars);
            }
        }
        
        $sidChars = array_reverse($sidChars);
        
        return implode('', $sidChars);
    }
    
    private static function getNextFeedValue($currentValue, $characters)
    {
        $currentPosition = strpos($characters, $currentValue);
        
        if($currentPosition === (strlen($characters) - 1))
            return null;
        else
            return $characters[$currentPosition + 1];
    }
}
