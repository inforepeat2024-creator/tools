import AbstractComponent from "../abstract_component.js";
import FormInputComponent from "./form_input_component.js";

export default class TextInput extends FormInputComponent {


    constructor() {
        super();


        Object.assign(this.state, {
            'element_type': 'text',

        });




    }



}

customElements.define('text-input', TextInput);
