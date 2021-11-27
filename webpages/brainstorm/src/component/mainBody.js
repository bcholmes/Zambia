import React, { Component } from 'react';
import axios from 'axios';

import Alert from 'react-bootstrap/Alert';
import Button from 'react-bootstrap/Button';
import Card from 'react-bootstrap/Card';
import Form from 'react-bootstrap/Form';
import Spinner from 'react-bootstrap/Spinner';

import SubmissionForm from './submissionForm';

class MainBody extends Component {

    constructor(props) {
        super(props);

        this.state = {
            loading: true,
            message: null
        }
    }

    componentDidMount() {
        this.loadInitialData();
    }

    render() {
        if (this.state.loading) {
            return (<div className="text-center">
                    <Spinner as="span" animation="border" role="status" aria-hidden="true">
                        <span className="sr-only">Loading...</span>
                    </Spinner>
                </div>);
        } else if (this.state.message) {
            return (<Alert variant={this.state.message.severity}>{this.state.message.text}</Alert>);
        } else {
            return (<SubmissionForm />);
        }
    }

    loadInitialData() {
        axios.post('/api/brainstorm/load_brainstorm.php')
        .then(res => {
            this.setState({
                ...this.state,
                loading: false,
                message: null
            });
        })
        .catch(error => {
            this.setState({
                ...this.state,
                loading: false,
                message: {
                    severity: "danger",
                    text: "Sorry. We're had a bit of a technical problem. Try again later?"
                }
            });
        });
    }
}

export default MainBody;