import AbstractComponent from "../abstract_component.js";
import FormInputComponent from "./form_input_component.js";

export default class CheckboxInput extends FormInputComponent {


    constructor() {
        super();



        Object.assign(this.state, {
            'element_type': 'checkbox',
            'element_checked': this.getAttribute('element_checked') ?? 0,

        });




    }

    render()
    {



        let included_classes = 'form-control';

        if(['checkbox'].includes(this.state.element_type))
            included_classes = "";

        this.innerHTML = `
    <div class="form-group">
        <label>
            ${this.state.element_label}
             <input
                type="${this.state.element_type}"
                ${this.renderInputMode()}
                ${this.renderRequired()}
                name="${this.state.element_name}"
                value="${this.state.element_value}"
                class="${included_classes} ${this.state.element_class}"
                placeholder="${this.state.element_placeholder}"
            >
        </label>

    </div>

    `;

        this.afterRender();

        this.attachListeners();

    }

    afterRender() {
        super.afterRender();
        if(this.state.element_checked == 1)
            this.querySelector('input').setAttribute('checked', true);
    }


}

customElements.define('checkbox-input', CheckboxInput);
