import {EventEmitter} from 'main.core.events';
import {Cache, Tag, Event, Loc} from 'main.core';

import './css/zeroing.css';

export default class Zeroing extends EventEmitter
{
	constructor()
	{
		super();
		this.cache = new Cache.MemoryCache();
		this.setEventNamespace('BX.Landing.UI.Field.Color.Zeroing');
		Event.bind(this.getLayout(), 'click', () => this.onClick());
	}

	getLayout(): HTMLElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`<div 
				title="${Loc.getMessage('LANDING_FIELD_COLOR-ZEROING_TITLE')}"
				class="landing-ui-field-color-preset-item landing-ui-field-color-zeroing"
			></div>`;
		});
	}

	onClick()
	{
		this.emit('onChange', {color: null});
	}
}
