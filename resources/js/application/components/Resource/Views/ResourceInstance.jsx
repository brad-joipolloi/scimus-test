import React, {Component, Fragment} from 'react';
import PropTypes from 'prop-types';
import Api from "../../../../helpers/Api";
import {Button, Card, CardBody, CardHeader, FormGroup} from "reactstrap";
import {BounceLoader} from "react-spinners";

import {ucwords} from "locutus/php/strings";
import {each, get, has, keys} from "lodash";

import Field from "../Interface/Instance/Form/Field";
import {Link} from "react-router-dom";

class ResourceInstance extends Component {
    constructor(props) {
        super(props);

        this._api = new Api(props.resourceName);

        const initialResourceInstance = {};

        each(this._api._resourceFields, field => {
            switch (field.type) {
                case 'text':
                    initialResourceInstance[field.name] = '';
                    break;
                case 'select':
                    initialResourceInstance[field.name] = field.multiple ? [] : '';
                    break;
            }
        });

        this.state = {
            resourceInstance: initialResourceInstance,
            resourceInstanceLoading: !! keys(this.props.resource).length,
            resourceInstanceErrors: {},
            isCreating: ! keys(this.props.resource).length,
        };

        this.flush                          = this.flush.bind(this);
        this.handleFieldChange              = this.handleFieldChange.bind(this);
        this.handleErrorOnInstanceUpdate    = this.handleErrorOnInstanceUpdate.bind(this);
        this.getErrorsForField              = this.getErrorsForField.bind(this);
        this.getResourceInstanceTitleValue  = this.getResourceInstanceTitleValue.bind(this);
        this.requestInstance                = this.requestInstance.bind(this);
        this.setInstance                    = this.setInstance.bind(this);
        this.updateInstance                 = this.updateInstance.bind(this);

        this.resourceInstanceActions = [];
    }

    componentDidMount() {
        if (!this.state.isCreating) {
            this.requestInstance();
        }
    }

    requestInstance() {
        this.setState(prevState => ({
            ...prevState,
            resourceInstanceLoading: true,
        }), () => {
            this._api.request('show', this.props.resource, this.props.resource)
                .then(response => {
                    this.setInstance(response.data.data);
                });
        });
    }

    setInstance(instance) {
        if (this._api._resourceActions.show.actions) {
            this.resourceInstanceActions = [];

            each(this._api._resourceActions.show.actions, action => {
                const hasDisplayCondition = has(action, 'display_condition');
                const displayConditionsPassed = [];

                if (hasDisplayCondition) {
                    let displayCondition = get(action, 'display_condition');

                    each(displayCondition, (value, field) => {
                        if (field === 'PERMISSION') {
                            return displayConditionsPassed.push(User.can(value));
                        }

                        if (value.constructor === Boolean) {
                            return displayConditionsPassed.push(!!get(instance, field) === value);
                        }

                        if (value.constructor === String || value.constructor === Number) {
                            return displayConditionsPassed.push(get(instance, field) === value);
                        }

                        if (value.constructor === Array) {
                            return displayConditionsPassed.push(value.includes(get(instance, field)));
                        }
                    });
                }

                if (!hasDisplayCondition || !displayConditionsPassed.includes(false)) {
                    this.resourceInstanceActions.push({
                        label: action.label,
                        callback: this._api.action(action, instance, {
                            path: (path) => this.props.history.push(path),
                            requestInstance: () => { this.requestInstance() },
                            setState: this.setState.bind(this),
                            getState: () => this.state,
                        }),
                    })
                }
            });
        }

        this.setState(prevState => ({
            ...prevState,
            resourceInstance: instance,
            resourceInstanceLoading: false,
        }));
    }

    flush() {
        if (this.state.resourceInstance.id === undefined) {
            this.createInstance();
        } else {
            this.updateInstance();
        }
    }

    updateInstance() {
        this._api.request('update', this.state.resourceInstance, this.state.resourceInstance)
            .then(response => {
                toastr.success(`${ucwords(this._api._resourceName)} has been updated`);
                this.setInstance(response.data.data);
            })
            .catch(this.handleErrorOnInstanceUpdate);
    }

    createInstance() {
        this._api.request('store', this.state.resourceInstance, this.state.resourceInstance)
            .then(response => {
                toastr.success(`${ucwords(this._api._resourceName)} has been created`);
                this.props.history.push(`/admin/${this.props.resourceName}s/${response.data.data.id}`);
                this.setInstance(response.data.data);
            })
            .catch(this.handleErrorOnInstanceUpdate);
    }

    getErrorsForField(field) {
        return get(this.state.resourceInstanceErrors, field.name, null);
    }

    getResourceInstanceTitleValue() {
        return (this._api._resourceLabelKey && has(this.state.resourceInstance, this._api._resourceLabelKey)) ?
            get(this.state.resourceInstance, this._api._resourceLabelKey) : '';
    }

    handleErrorOnInstanceUpdate(error) {
        if (get(error, 'response.status') === 422) {
            this.setState(prevState => ({
                ...prevState,
                resourceInstanceErrors: get(error, 'response.data.errors', {}),
            }))
        }
    }

    handleFieldChange(field, value) {
        this.setState(prevState => {
            const instance = {...prevState.resourceInstance};

            instance[field.name] = value;

            return {
                ...prevState,
                resourceInstance: instance,
            }
        });
    }

    render() {
        return (
            <Card>
                <CardHeader className={'d-flex justify-content-between'}>
                    <div>
                    {(this.props.resource && this.getResourceInstanceTitleValue() &&
                        <span>Viewing {this.getResourceInstanceTitleValue()}</span>
                    ) || (this.props.resource &&
                        <span>&nbsp;</span>
                    ) || (
                        <span>Creating new {this.props.resourceName}</span>
                    )}
                    </div>
                    <div>
                        {!this.state.resourceInstanceLoading &&
                            this.resourceInstanceActions.map(action =>
                                <Button key={`index-actions-${action.label}`}
                                        size={'xs'}
                                        className={'mx-1'}
                                        onClick={action.callback}
                                        color={action.color || 'primary'}
                                >
                                    {action.label}
                                </Button>
                        )}
                    </div>
                </CardHeader>

                {(this.state.resourceInstanceLoading &&
                    <CardBody className={'d-flex justify-content-center'}>
                        <BounceLoader/>
                    </CardBody>
                ) || (
                    <CardBody>
                        {this._api._resourceFields.map(field =>
                            (!this.state.isCreating || field.type !== 'resource_collection') &&
                                <Field field={field}
                                       fieldErrors={this.getErrorsForField(field)}
                                       handleFieldChange={this.handleFieldChange}
                                       history={this.props.history}
                                       isCreate={this.state.isCreating}
                                       location={this.props.location}
                                       key={field.name}
                                       value={field.type === 'resource_collection' ? this.props.resource : this.state.resourceInstance[field.name]}
                                />
                        )}

                        <FormGroup className={'row mb-0'}>
                            <div className={'offset-sm-2 col-sm-10 d-flex justify-content-between'}>
                                <Link className={'btn btn-secondary'}
                                      to={this.props.location.pathname.split('/').slice(0, -1).join('/')}
                                >Back</Link>
                                <Button color={'primary'} onClick={this.flush}>Save</Button>
                            </div>
                        </FormGroup>
                    </CardBody>
                )}
            </Card>
        );
    }
}

ResourceInstance.propTypes = {
    resource: PropTypes.shape({
        id: PropTypes.oneOfType([
            PropTypes.number,
            PropTypes.string
        ]),
    }),
    resourceName: PropTypes.string.isRequired,
};

export default ResourceInstance;
