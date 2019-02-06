import React, {Component} from 'react';
import PropTypes from 'prop-types';
import {FormGroup, Label} from "reactstrap";
import ReactSelect from "react-select";
import Api from "../../../../../../../helpers/Api";
import {ucwords} from "locutus/php/strings";
import {each, get, has, keys, last, sortBy} from "lodash";

class Select extends Component {
    constructor(props) {
        super(props);

        this.state = {
            options: props.options ? props.options : [],
        };

        this.handleFieldChange = this.handleFieldChange.bind(this);
        this.getOptionsFromResponse = this.getOptionsFromResponse.bind(this);
        this.mapOptionToSelect = this.mapOptionToSelect.bind(this);
    }

    handleFieldChange(value) {
        const multiple = this.props.field.multiple;
        const id_key = this.props.field.id_key.constructor === Array ?
            last(this.props.field.id_key) :
            this.props.field.id_key;

        this.props.handleFieldChange(
            this.props.field,
            multiple ?
                value.map(o => this.state.options.find(option => option[id_key] === o.value)) :
                this.state.options.find(option => option[id_key] === value.value)
        );
    }

    componentDidMount() {
        if (this.props.field.resource) {
            const api = new Api(this.props.field.resource);

            if (api.hasAction('index')) {
                const filters = {};

                if (has(this.props.field, 'resource_filters')) {
                    each(get(this.props.field, 'resource_filters'), (value, filter) => {
                        filters[`filter[${filter}]`] = value;
                    });
                }

                api.request('index', filters)
                    .then(response => {
                        console.log(response.data);
                        this.setState(prevState => ({
                            ...prevState,
                            options: this.getOptionsFromResponse(response.data.data),
                        }));
                    });
            }
        }
    }

    getOptionsFromResponse(data) {
        const keys = this.props.field.id_key;

        if (keys && keys.constructor === Array && keys.length > 1) {
            let optionsData = [];

            data.forEach(datum => {
                keys.forEach((key, index, list) => {
                    if (index === list.length - 1) {
                        return;
                    }

                    const prop = get(datum, key);

                    if (! prop) {
                        return;
                    }

                    if (prop.constructor) {
                        if (prop.constructor === Array) {
                            optionsData = optionsData.concat(prop);
                        }

                        if (prop.constructor === Object || prop.constructor === String) {
                            optionsData.push(prop);
                        }
                    }
                });
            });

            data = optionsData;
        }

        if (this.props.field.nullable) {
            data.unshift({
                label: 'None',
                value: '',
            });
        }

        return data;
    }

    mapOptionToSelect(option) {
        let label = 'none';
        let value = '';

        if (JSON.stringify(sortBy(keys(option))) === JSON.stringify(['label', 'value'])) {
            return option;
        }

        if (option) {
            if (option.constructor === Object) {
                let valueKey = this.props.field.id_key;
                if (this.props.field.id_key.constructor === Array) {
                    valueKey = last(this.props.field.id_key);
                }

                value = option[valueKey] ? option[valueKey] : "";

                label = this.props.field.label_key.map(key => has(option, key) ? get(option, key) : key).join(' ');
            }

            if (option.constructor === String) {
                value = option;
                label = option
            }
        }

        return {
            label: ucwords(label),
            value: value,
        };
    }

    render() {
        return (
            <ReactSelect name={'roles'}
                    onChange={this.handleFieldChange}
                    value={
                        this.props.defaultValue ?
                            (
                                this.props.field.multiple ?
                                    this.props.defaultValue.map(this.mapOptionToSelect) :
                                    this.mapOptionToSelect(this.props.defaultValue)
                            ) : this.mapOptionToSelect("")
                    }
                    options={this.state.options.map(this.mapOptionToSelect)}
                    isMulti={this.props.field.multiple}
                    isDisabled={this.props.field.readonly}
                    styles={{
                        container: (base) => ({
                            ...base,
                            flexGrow: 1,
                            flexShrink: 1,
                            flexBasis: 'auto',
                        }),
                        control: (base) => ({
                            ...base,
                            minHeight: 48,
                            backgroundColor: '#f7f7f9',
                            border: 'none',
                            borderRadius: 'none',
                            fontSize: '0.7875rem',
                        }),
                        dropdownIndicator: (base) => ({
                            ...base,
                            paddingTop: 0,
                            paddingBottom: 0,
                        }),
                        clearIndicator: (base) => ({
                            ...base,
                            paddingTop: 0,
                            paddingBottom: 0,
                        }),
                        valueContainer: (base) => ({
                            ...base,
                            paddingLeft: '1.75rem',
                            paddingRight: '1.75rem',
                        })
                    }}
            />
        );
    }
}

Select.propTypes = {
    defaultValue: PropTypes.oneOfType([PropTypes.string, PropTypes.array, PropTypes.object]),
    handleFieldChange: PropTypes.func.isRequired,
    field: PropTypes.object.isRequired,
};

export default Select;