<?php
namespace Ucenter\Utils\MyValidator;

use Phalcon\Validation\Validator,
    Phalcon\Validation\ValidatorInterface,
    Phalcon\Validation\Message;

class NumsValidator extends Validator implements ValidatorInterface
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
        $flag = true;
        $value = $validator->getValue($attribute);
        $errorCode = $this->getOption('code');

        if(!is_array($value))
        {
            $message = '参数必须是数组';
            $validator->appendMessage(new Message($message, $attribute, 'Nums'));
            return false;
        }

        $countVal = count($value);

        $ruleMin = $this->getOption('min');
        $ruleMax = $this->getOption('max');

        if ($ruleMin == $ruleMax) 
        {
            if ($countVal < $ruleMin) 
            {
                $flag = false;
            }
        }
        else
        {
            if ($countVal < $ruleMin || $countVal > $ruleMax)
            {
                $flag = false;
            }
        }

        if (!$flag) {

            $message = $this->getOption('message');
            if (!$message)
            {
                $message = 'The num is not valid';
            }

            $validator->appendMessage(new Message($message, $attribute, 'Nums'));

            return false;
        }

        return true;
    }

}