import React, {Component} from 'react';
import SectionTitle from "./SectionTitle";
import SectionImage from "./SectionImage";
import SectionTextImage from "./SectionTextImage";

export default class FormSection extends Component {
    _components = {
        title: SectionTitle,
        image: SectionImage,
        textImage: SectionTextImage,
    };

    getComponent() {
        const Component = this._components[this.props.data.data.type];

        return <Component {...this.props} />
    }

    render() {
        return this.getComponent(this.props.data.data.type);
    }
}