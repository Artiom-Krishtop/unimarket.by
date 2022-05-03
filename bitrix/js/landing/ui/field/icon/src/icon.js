import {Runtime} from 'main.core'
import {IconPanel} from 'landing.ui.panel.iconpanel';
import {Image} from 'landing.ui.field.image'

/**
 * @memberOf BX.Landing.UI.Field
 */
export class Icon extends Image
{
	constructor(data)
	{
		super(data);
		this.uploadButton.layout.innerText = BX.Landing.Loc.getMessage("LANDING_ICONS_FIELD_BUTTON_REPLACE");
		this.editButton.layout.hidden = true;
		this.clearButton.layout.hidden = true;

		this.dropzone.removeEventListener("dragover", this.onDragOver);
		this.dropzone.removeEventListener("dragleave", this.onDragLeave);
		this.dropzone.removeEventListener("drop", this.onDrop);
		this.preview.removeEventListener("dragenter", this.onImageDragEnter);

		IconPanel
			.getLibraries()
			.then(function (libraries)
			{
				if (libraries.length === 0)
				{
					this.uploadButton.disable();
				}
			}.bind(this));
	}

	onUploadClick(event)
	{
		event.preventDefault();

		IconPanel
			.getInstance()
			.show()
			.then(function (iconClassName)
			{
				this.setValue({type: "icon", classList: iconClassName.split(" ")});
			}.bind(this));
	}

	isChanged()
	{
		return this.getValue().classList.some(function (className)
		{
			return this.content.classList.indexOf(className) === -1;
		}, this);
	}

	getValue()
	{
		var classList = this.classList;

		if (this.selector)
		{
			var selectorClassname = this.selector.split("@")[0].replace(".", "");
			classList = Runtime.clone(this.classList).concat([selectorClassname]);
			classList = BX.Landing.Utils.arrayUnique(classList);
		}

		return {
			type: "icon",
			src: "",
			id: -1,
			alt: "",
			classList: classList,
			url: Object.assign({}, this.url.getValue(), {enabled: this.urlCheckbox.checked}),
		};
	}

	reset()
	{
		this.setValue({
			type: "icon",
			src: "",
			id: -1,
			alt: "",
			classList: [],
			url: '',
		});
	}
}