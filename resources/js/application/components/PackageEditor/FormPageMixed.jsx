import React, {Component} from 'react';
import PropTypes from 'prop-types';
import {FormGroup, Input, Label} from "reactstrap";
import Asset from "./Form/Asset";
import Select from "./Form/Select";

class FormPageMixed extends Component {
    constructor(props) {
        super(props);

        this.handleFormChange   = this.handleFormChange.bind(this);
        this.handleBSFormChange = this.handleBSFormChange.bind(this);
    }

    handleFormChange(field, value) {
        this.props.handlePackageDataChange(field, value);
    }

    handleBSFormChange(event) {
        const field = event.target.name;
        const value = event.target.value;

        this.props.handlePackageDataChange(field, value);
    }
    render() {
        return (
            <div>
                <FormGroup>
                    <Label>Title</Label>
                    <Input bsSize={'sm'}
                           name={'title'}
                           value={this.props.data.data.title}
                           onChange={this.handleBSFormChange}
                    />
                </FormGroup>

                <FormGroup>
                    <Label>Title Image</Label>
                    <Asset name={'titleImage'}
                           value={this.props.data.data.titleImage}
                           packageId={this.props.packageId}
                           packageVersionId={this.props.packageVersionId}
                           onChange={this.handleFormChange}
                           assetType={'titleImage'}
                    />
                </FormGroup>
                {/*{JSON.stringify(this.props.data)}*/}
            </div>
        );
    }
}

FormPageMixed.propTypes = {

};

export default FormPageMixed;