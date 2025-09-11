import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

export default class extends Controller {
    async initialize() {
        // this.component = await getComponent(this.element);
        this.component = this.element.__component;
    }
    async connect() {

        this.component = await getComponent(this.element);
    }

    toggleMode() {

        // this.component.action('save', { arg1: 'value1' });
        // this.component.set('mode', 'editing');
        // this.component.render();

        
    }
    editMode() {
        this.component.set('mode', 'editing');
    }
    saveMode() {
        this.component.action('save', { arg1: 'value1' });
    }
}