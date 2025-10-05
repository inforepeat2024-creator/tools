import AbstractComponent from "../abstract_component.js";
import FormInputComponent from "./form_input_component.js";

export default class UserIdInput extends FormInputComponent {


    constructor() {
        super();


        Object.assign(this.state, {
            'element_type': 'hidden',
            'element_name': this.getAttribute('element_name') ?? "user_id",

        });




    }

    render(){
        this.innerHTML = `<input type="hidden" name="${this.state.element_name}" value="${this.state.element_value}">`;
    }



}

customElements.define('user-id-input', UserIdInput);
