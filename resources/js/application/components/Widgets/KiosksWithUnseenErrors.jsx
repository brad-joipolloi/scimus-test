import React, {Component} from 'react';
import {Card, CardBody, CardHeader, Table} from "reactstrap";
import {Link} from "react-router-dom";
import {BounceLoader} from "react-spinners";

export default class KiosksWithUnseenErrors extends Component {
    constructor(props) {
        super(props);

        this.state = {kiosks: [], loading: true};

        this.getPackagesPendingApproval = this.getPackagesPendingApproval.bind(this);
    }

    componentDidMount() {
        this.getPackagesPendingApproval();
    }

    getPackagesPendingApproval() {
        axios.get('/api/kiosk', {
            params: {
                'filter[unseen_errors]': true,
            }
        }).then(response => {
            this.setState(prevState => ({
                ...prevState,
                loading: false,
                kiosks: response.data.data,
            }))
        })
    }

    render() {
        return (
            <Card>
                <CardHeader className={'text-dark'}>
                    Kiosks with unseen errors
                </CardHeader>
                <CardBody className={'p-0'}>
                    {!this.state.loading && ((this.state.kiosks.length &&
                        <Table size={'sm'} className={'mb-0'} borderless>
                            <tbody>
                            {this.state.kiosks.map(kiosk =>
                                <tr key={`unregistered-kiosks-${kiosk.id}`}>
                                    <td>
                                        {kiosk.identifier}
                                    </td>
                                    <td className={'text-right'}>
                                        <Link className={'btn btn-xs btn-secondary my-auto'}
                                              to={`/admin/kiosks/${kiosk.id}`}
                                        >
                                            View
                                        </Link>
                                    </td>
                                </tr>
                            )}
                            </tbody>
                        </Table>
                    ) || (
                        <div className={'text-center p-3'}>
                            <strong>No kiosks have unseen errors</strong>
                        </div>
                    ))}
                    {this.state.loading &&
                    <div className={'d-flex justify-content-center p-3'}>
                        <BounceLoader/>
                    </div>
                    }
                </CardBody>
            </Card>
        );
    }
}
