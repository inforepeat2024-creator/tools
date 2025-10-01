<?php

namespace RepeatToolkit\Helpers\Traits;

use Illuminate\Support\Str;
use Modules\Core\Utilities\FormBuilder\FormInputs\AbstractFormInput;
use Modules\Core\Utilities\FormBuilder\FormInputs\CustomHtmlInput;
use Modules\Core\Utilities\FormBuilder\FormInputs\HiddenInput;
use Modules\Core\Utilities\FormBuilder\FormInputs\HorizontalButtonsRadioInput;
use Modules\Core\Utilities\FormBuilder\FormInputs\HorizontalRadioButtonsWithText;

trait FormsTrait
{
    use LocaleTrait;


    /**
     * @param AbstractFormInput[] $form_inputs
     * @return array
     */
    public function getClientSideValidationFieldsFromInputs(array $form_inputs) : array
    {


        $validation_array = [];

        $obj = new \stdClass();


        foreach ($form_inputs as $form_input)
        {
            if($form_input instanceof HorizontalButtonsRadioInput )
                continue    ;

            if($form_input instanceof HorizontalRadioButtonsWithText )
                continue    ;

            if($form_input instanceof CustomHtmlInput )
                continue    ;

            if($form_input instanceof HiddenInput )
                continue    ;

            if(!isset($validation_array[$form_input->getName()]))
            {
                $validation_array[$form_input->getName()] = ["validators" => []];
            }

            foreach ($form_input->getValidatorRules() as $validator)
            {
                if(Str::contains(get_class($form_input), ['ranslat']))
                {
                    $validation_array[$form_input->getGivenLanguageColumnAlias($this->getCurrentLanguageId())]["validators"][$validator->getClientSideSlug()] = ["message" => $validator->getMessage()];
                }
                else
                {
                    $validation_array[$form_input->getName()]["validators"][$validator->getClientSideSlug()] = ["message" => $validator->getMessage()];
                }


            }
        }

      /*  foreach ($form_inputs as $form_input)
        {
            if(!isset($obj->{$form_input->getName()}))
            {
                $obj->{$form_input->getName()} = new \stdClass();
                $obj->{$form_input->getName()}->validators = new \stdClass();

            }

            foreach ($form_input->getValidatorRules() as $validator)
            {
                $obj->{$form_input->getName()}->validators->{$validator->getClientSideSlug()} = new \stdClass();
                $obj->{$form_input->getName()}->validators->{$validator->getClientSideSlug()}->message = $validator->getMessage();
            }
        }*/

       // dd($validation_array);


        return $validation_array;

    }

}
