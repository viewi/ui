<?php

namespace Viewi\UI\Components\Forms;

class FormContext
{
    public array $inputs = [];

    public function validate(array $ruleList): bool
    {
        $finalValid = true;
        $generalMessages = [];
        foreach ($ruleList as $field => $rules) {
            $messages = [];
            foreach ($rules as $_ => $action) {
                $validationResult = $action();
                $valid = $validationResult === true;
                $finalValid = $finalValid && $valid;
                if (!$valid) {
                    $messages[] = $validationResult || 'Validation has failed';
                }
            }
            /**
             * @var callback
             */
            $inputCallback = $this->inputs[$field] ?? false;
            if (count($messages) > 0) {
                if ($inputCallback) {
                    $inputCallback(false, $messages);
                } else {
                    $generalMessages = array_merge($generalMessages, $messages);
                }
            } else {
                if ($inputCallback) {
                    $inputCallback(true, $messages);
                }
            }
        }
        /**
         * @var callback
         */
        $fallbackMessagesCallback = $this->inputs['fallbackMessages'] ?? false;

        if ($fallbackMessagesCallback) {
            if ($generalMessages) {
                $fallbackMessagesCallback(false, $generalMessages);
            } else {
                $fallbackMessagesCallback(true, $generalMessages);
            }
        }
        return $finalValid; // $valid;
    }
}
