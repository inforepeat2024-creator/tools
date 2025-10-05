
import FormInputComponent from "./form_input_component.js";

export default class TextAreaInput extends FormInputComponent {


    constructor() {
        super();

        Object.assign(this.state, {'element_rows': this.getAttribute('element_rows') ?? ""});


    }

    render() {



        let options = ``;



        let required = this.state.element_required ? "required" : "";

        this.innerHTML = `
<div class="form-group">
    ${this.getLabel()}
    <textarea
        class="form-control ${this.state.element_class}"
        name="${this.state.element_name}"
        ${required}
        rows="${this.state.element_rows ?? 3}"
    >${this.state.element_value ?? ""}</textarea>
</div>
         `;


        this.afterRender();
        this.attachListeners();
    }



}

customElements.define('textarea-input', TextAreaInput);
