import React, { Component } from 'react';
import axios from 'axios';

import Alert from 'react-bootstrap/Alert';
import Button from 'react-bootstrap/Button';
import Card from 'react-bootstrap/Card';
import Form from 'react-bootstrap/Form';
import Spinner from 'react-bootstrap/Spinner';

import store from '../state/store';

class SubmissionForm extends Component {

    constructor(props) {
        super(props);

        this.state = {
            values: this.createInitialValues(),
            submitAllowed: (store.getState().auth.jwt != null),
            errors: {}
        }
    }

    createInitialValues() {
        let divisions = store.getState().options.divisions;
        let values = {};
        if (divisions && divisions.length === 1) {
            values['division'] = divisions[0].id.toString();
        }
        return values;
    }


    componentDidMount() {
        this.unsubscribe = store.subscribe(() => {
            let values = this.createInitialValues();
            let submitAllowed = (store.getState().auth.jwt != null);
            this.setState({
                ...this.state,
                values: values,
                submitAllowed: submitAllowed
            });
        });
    }

    componentWillUnmount() {
        if (this.unsubscribe) {
            this.unsubscribe();
        }
    }

    render() {
        let message = this.state.message ? (<Alert variant={this.state.message.severity}>{this.state.message.text}</Alert>) : undefined;
        let message2 = this.state.submitAllowed ?  undefined : (<Alert variant="warning">Please log in to submit session ideas.</Alert>);
        const spinner = this.state.loading ? (<Spinner
            as="span"
            animation="border"
            size="sm"
            role="status"
            aria-hidden="true"
        />) : undefined;

        let options = store.getState().options.divisions ? store.getState().options.divisions.map((d) => { return (<option value={d.id} key={d.id}>{d.name}</option>)}) : undefined;
        let tracks = this.getTrackOptions().map((t) => {return (<option value={t.trackid} key={t.trackid}>{t.trackname}</option>) });

        return (
            <Form onSubmit={(e) =>  this.submitForm(e)}>
                {message}
                {message2}

                <Card>
                    <Card.Header><h2>Submit a Session</h2></Card.Header>
                    <Card.Body>
                        <p>Submissions are open for programming for WisCon 2022.</p>

                        <Form.Group controlId="division">
                            <Form.Label className="sr-only">Division:</Form.Label>
                            <Form.Control as="select" className={this.getErrorClass('division')} value={this.getFormValue('division')} onChange={(e) => this.setFormValue("division", e.target.value)} key="divsion">
                                <option value="" key="empty">Please select a division (Required)</option>
                                {options}
                            </Form.Control>
                            <Form.Text className="text-muted">What kind of session is this? e.g. Panels, Academic, etc.</Form.Text>
                        </Form.Group>

                        <Form.Group controlId="title">
                            <Form.Label className="sr-only">Title</Form.Label>
                            <Form.Control className={this.getErrorClass('title')} type="text" placeholder="Title (Required)" value={this.getFormValue('title')} onChange={(e) => this.setFormValue('title', e.target.value)} />
                        </Form.Group>

                        <Form.Group controlId="progguiddesc">
                            <Form.Label className="sr-only">Description</Form.Label>
                            <Form.Control as="textarea" rows={3} className={this.getErrorClass('progguiddesc')} type="text" placeholder="Session description (Required)" value={this.getFormValue('progguiddesc')} onChange={(e) => this.setFormValue('progguiddesc', e.target.value)} />
                            <Form.Text className="text-muted">Max 500 characters</Form.Text>
                        </Form.Group>

                        <Form.Group controlId="track">
                            <Form.Label className="sr-only">Track:</Form.Label>
                            <Form.Control as="select" className={this.getErrorClass('track')} value={this.getFormValue('track')} onChange={(e) => this.setFormValue("track", e.target.value)} key="track">
                                <option value="" key="empty">Please select a track (Required)</option>
                                {tracks}
                            </Form.Control>
                            <Form.Text className="text-muted">Make a best guess about what track this panel should belong to</Form.Text>
                        </Form.Group>

                        <Form.Group controlId="servicenotes">
                            <Form.Label className="sr-only">Equipment Needed / Alternative Format</Form.Label>
                            <Form.Control as="textarea" rows={3} className={this.getErrorClass('servicenotes')} type="text" placeholder="Equipment Needed / Alternative Format" value={this.getFormValue('servicenotes')} onChange={(e) => this.setFormValue('servicenotes', e.target.value)}/>
                        </Form.Group>

                        <Form.Group controlId="persppartinfo">
                            <Form.Label className="sr-only">Suggest Some Good Participants</Form.Label>
                            <Form.Control as="textarea" rows={3} className={this.getErrorClass('persppartinfo')} type="text" placeholder="Can you suggest some good participants? Or a moderator?" value={this.getFormValue('persppartinfo')} onChange={(e) => this.setFormValue('persppartinfo', e.target.value)}/>
                        </Form.Group>

                    </Card.Body>
                    <Card.Footer>
                        <Button variant="primary" type="submit" disabled={!this.state.submitAllowed}>{spinner} <span>Submit</span></Button>
                    </Card.Footer>
                </Card>
            </Form>
        )
    }

    getTrackOptions() {
        if (this.state.values['division'] && store.getState().options.divisions) {
            let division = null;
            let divisionId = this.state.values['division'];
            store.getState().options.divisions.forEach((element) => { if (element.id.toString() === divisionId) { division = element; } } )
            return division ? division.tracks : [];
        } else {
            return [];
        }
    }

    getErrorClass(name) {
        return this.isFieldInError(name) ? "is-invalid" : "";
    }

    isFieldInError(name) {
        let errors = this.state.errors;
        if (errors) {
            return errors[name];
        } else {
            return false;
        }
    }

    getFormValue(formName) {
        if (this.state.values) {
            return this.state.values[formName] || '';
        } else {
            return '';
        }
    }

    setFormValue(formName, formValue) {
        let state = this.state;
        let value = state.values || {};
        let newValue = { ...value };
        let errors = this.state.errors || {};
        newValue[formName] = formValue;
        errors[formName] = !this.validateValue(formName, formValue);
        this.setState({
            ...state,
            values: newValue,
            message: null,
            errors: errors
        });
    }

    validateValue(formName, formValue) {
        if (formName === 'title') {
            return formValue != null && formValue !== '';
        } else if (formName === 'progguiddesc') {
            return formValue != null && formValue != '' && formValue.length <= 500;
        } else if (formName === 'division') {
            return formValue != null && formValue != '';
        } else if (formName === 'track') {
            return formValue != null && formValue != '';
        } else {
            return true;
        }
    }

    isValidForm() {
        let formKeys = [ 'title', 'progguiddesc', 'track', 'division' ];
        let errors = this.state.errors || {};
        let valid = true
        formKeys.forEach(element => {
            let v = this.validateValue(element, this.state.values[element]);
            valid &= v;
            errors[element] = !v;
        });

        let message = null;
        if (!valid) {
            message = { severity: "danger", text: "Whoopsie-doodle. It looks like some of this information isn't right."}
        }
        this.setState({
            ...this.state,
            errors: errors,
            message: message
        })
        return valid;
    }

    submitForm(event) {
        event.preventDefault();
        event.stopPropagation();
        const form = event.currentTarget;

        if (this.isValidForm(form)) {
            this.setState({
                ...this.state,
                loading: true
            });
    
            axios.post('/api/brainstorm/submit_session.php', this.state.values, {
                headers: {
                    "Authorization": "Bearer " + store.getState().auth.jwt
                }
            })
            .then(res => {
                this.setState({
                    ...this.state,
                    values: this.createInitialValues(),
                    errors: {},
                    loading: false,
                    message: {
                        severity: "success",
                        text: "Thanks for the submission. Suggest another!"
                    }
                });
            })
            .catch(error => {
                this.setState({
                    ...this.state,
                    loading: false,
                    message: {
                        severity: "danger",
                        text: "Sorry. We're had a bit of a technical problem. Try again?"
                    }
                });
            });
        }
    }
}

export default SubmissionForm;