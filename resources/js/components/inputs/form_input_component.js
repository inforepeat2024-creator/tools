import AbstractComponent from "../abstract_component.js";
import UrlHelper from "../../helpers/url_helper.js";

export default class FormInputComponent extends AbstractComponent {


    constructor() {
        super();


        Object.assign(this.state, {
            'element_type': this.getAttribute('element_type') ?? "",
            'element_name': this.getAttribute('element_name') ?? "",
            'element_id': this.getAttribute('element_id') ?? "",
            'element_class': this.getAttribute('element_class') ?? "",
            'element_style': this.getAttribute('element_style') ?? "",
            'element_icon': this.getAttribute('element_icon') ?? "",
            'element_placeholder': this.getAttribute('element_placeholder') ?? "",
            'element_value': this.getAttribute('element_value') ?? UrlHelper.getParam(this.getAttribute('element_name') ?? "", ""),
            'element_label': this.getAttribute('element_label') ?? "",
            'element_required': this.getAttribute('element_required') ?? 0,
            'inputmode': this.getAttribute('inputmode') ?? "",
        });




    }

    getLabel()
    {
        if(this.state.element_label == "")
            return ``;

        return ` <label>${this.state.element_label}</label>`;

    }

    renderInputMode()
    {
        let input_mode = "";
        if(this.state.inputmode != "")
        {
            input_mode = `inputmode="${this.state.inputmode}"`;
        }

        return input_mode;
    }

    renderRequired()
    {
        let required = "";
        if(this.state.element_required == 1)
        {
            required = "required";
        }

        return required;
    }

    render()
    {



        let included_classes = 'form-control';

        if(['checkbox'].includes(this.state.element_type))
            included_classes = "";

        this.innerHTML = `
    <div class="form-group">
        ${this.getLabel()}
        <input
        type="${this.state.element_type}"
        ${this.renderInputMode()}
        ${this.renderRequired()}
        name="${this.state.element_name}"
        value="${this.state.element_value}"
        class="${included_classes} ${this.state.element_class}"
        placeholder="${this.state.element_placeholder}">
    </div>

    `;

        this.afterRender();

        this.attachListeners();

    }

    afterRender()
    {
        if(this.state.element_required == 0)
            return false;
        let label =this.querySelector('label')

        if(label){
            label.innerHTML += `<span class="text-danger ms-2">*</span>`;
        }
    }

    connectedCallback()
    {
        console.log('FormInputComponent::' + this.state.element_type);
        this.render();
    }

}


customElements.define('form-input-component', FormInputComponent);
