
import AbstractComponent from "../abstract_component.js";
import __i from "../../vendor/repeat-toolkit/i18n.js";


export class AbstractForm extends AbstractComponent {
    constructor() {
        super();

        Object.assign(this.state, {
            method: this.getAttribute("method") ?? "POST",
            action: this.getAttribute('action') ?? "",
            model: JSON.parse(this.getAttribute('model') ?? '{}')
        });

    }

    connectedCallback() {


        console.log('AbstractForm connected');

        this.render();

    }

    renderSubmitButton()
    {
        return `     <button type="submit" class="btn btn-primary w-100 btn-lg">${__i("Sačuvaj")} <span class="fa fa-save"></span></button>
               `;
    }

    renderFormHtml()
    {

    }

    render()
    {

        this.innerHTML = `
            <form method="${this.state.method}" action="${this.state.action}">


            </form>

        `;

        this.querySelector('form').innerHTML = this.renderFormHtml();

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = (document.querySelector('meta[name="csrf-token"]') != null ? document.querySelector('meta[name="csrf-token"]').content : "");

        this.querySelector('form').appendChild(csrf);


        this.markRequiredFields();

        this.attachListeners();
    }

    markRequiredFields()
    {

    }

}

customElements.define('abstract-form', AbstractForm);
