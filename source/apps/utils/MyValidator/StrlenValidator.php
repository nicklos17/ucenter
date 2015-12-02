<?php
namespace Ucenter\Utils\MyValidator;

use Phalcon\Validation\Validator,
    Phalcon\Validation\ValidatorInterface,
    Phalcon\Validation\Message;

class StrlenValidator extends Validator implements ValidatorInterface
{

    /**
     * Executes the validation
     *
     * @param Phalcon\Validation $validator
     * @param string $attribute
     * @return boolean
     */
    public function validate(\Phalcon\Validation $validator, $attribute)
    {
        $value = $validator->getValue($attribute);
        $errorCode = $this->getOption('code');
        $ruleMin = $this->getOption('min');
        $ruleMax = $this->getOption('max');

        $valLen = $this->strLength($value);     // 计算字符长度

        if ($valLen < $ruleMin || $valLen > $ruleMax) {
            $message = $this->getOption('message');
            if (!$message)
            {
                $message = 'The length is not valid';
            }

            $validator->appendMessage(new Message($message, $attribute, 'length'));

            return false;
        }

        return true;
    }

    function strLength($str){
        return (strlen($str) + mb_strlen($str,'UTF8')) / 2;
    }
}