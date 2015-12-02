<?php
namespace Ucenter\Utils\MyValidator;

use Phalcon\Validation\Validator,
    Phalcon\Validation\ValidatorInterface,
    Phalcon\Validation\Message;

class FileTypeValidator extends Validator implements ValidatorInterface
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
        $reqType = $validator->getValue($attribute);  // 待校验的文件集合
        $fileType = $this->getOption('filetype');     // 合法的文件类型集合
        $errorCode = $this->getOption('code');

        foreach ($reqType as $file)
        {
            $extArr = explode('.', $file);
            $ext = array_pop($extArr);
            if (!in_array($ext, $fileType))
            {
                $flag = false;
                break;
            }
        }

        if (!$flag)
        {
            $message = $this->getOption('message');
            if (!$message)
            {
                $message = 'The filetype is not valid';
            }

            $validator->appendMessage(new Message($message, $attribute, 'filetype'));

            return false;
        }
        return true;
    }

}