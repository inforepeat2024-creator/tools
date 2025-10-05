import AbstractComponent from "../abstract_component.js";
import FormInputComponent from "./form_input_component.js";

export default class SelectInput extends FormInputComponent {


    constructor() {
        super();


        Object.assign(this.state, {
            'element_collection': JSON.parse(this.getAttribute('element_collection') ?? "[]"),

        });




    }

    render() {



        let options = ``;


        Object.entries(this.state.element_collection).forEach(([value, label] )=> {
            if(value == "")
                options += `<option value="${value}" ${value == this.state.element_value ? "selected" : ""}>${label}</option>`;
        })

        Object.entries(this.state.element_collection).forEach(([value, label] )=> {
            if(value != "")
                options += `<option value="${value}" ${value == this.state.element_value ? "selected" : ""}>${label}</option>`;
        })

        let required = this.state.element_required ? "required" : "";

        this.innerHTML = `
<div class="form-group">
    ${this.getLabel()}
    <select
        class="form-select ${this.state.element_class}"
        name="${this.state.element_name}"
        ${required}
    >
        ${options}
    </select>
</div>
         `;

        this.afterRender();
        this.attachListeners();


    }




}

customElements.define('select-input', SelectInput);
