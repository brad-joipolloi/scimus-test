import React, {Component} from 'react';
import {Card, CardBody, CardFooter, CardTitle} from "reactstrap";
import PropTypes from 'prop-types';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {Link} from "react-router-dom";

class AuditLog extends Component {
    constructor(props) {
        super(props);

    }

    render() {
        return (
            <Card>
                <CardBody>
                    <CardTitle>
                        Recent Kiosk Actions
                    </CardTitle>

                </CardBody>
                <CardFooter className={'text-small'}>
                    <Link to={'/admin/kiosks'} className={'d-flex justify-content-between'}>
                        <span className={'my-auto'}>View all kiosks</span>
                        <FontAwesomeIcon icon={['fal', 'angle-double-right']} className={'my-auto'} />
                    </Link>
                </CardFooter>
            </Card>
        );
    }
}

AuditLog.propTypes = {};

export default AuditLog;
