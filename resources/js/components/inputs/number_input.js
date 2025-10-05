import AbstractComponent from "../abstract_component.js";
import FormInputComponent from "./form_input_component.js";

export default class NumberInput extends FormInputComponent {


    constructor() {
        super();


        Object.assign(this.state, {
            'element_type': 'number',

        });




    }



}

customElements.define('number-input', NumberInput);
