import React, { Component } from 'react';

import Button from 'react-bootstrap/Button';
import Card from 'react-bootstrap/Card';
import Form from 'react-bootstrap/Form';

class SubmissionForm extends Component {

    constructor(props) {
        super(props);

        this.state = {
            values: {},
            errors: {}
        }
    }

    render() {
        return (
            <Form>
                <Card>
                    <Card.Header><h2>Submit a Session</h2></Card.Header>
                    <Card.Body>
                        <Form.Group controlId="title">
                            <Form.Label className="sr-only">Title</Form.Label>
                            <Form.Control className={this.getErrorClass('title')} type="text" placeholder="Title" value={this.getFormValue('title')} onChange={(e) => this.setFormValue('title', e.target.value)}/>
                        </Form.Group>

                        <Form.Group controlId="progguiddesc">
                            <Form.Label className="sr-only">Description</Form.Label>
                            <Form.Control as="textarea" rows={3} className={this.getErrorClass('progguiddesc')} type="text" placeholder="Session description" value={this.getFormValue('progguiddesc')} onChange={(e) => this.setFormValue('progguiddesc', e.target.value)}/>
                        </Form.Group>

                        <Form.Group controlId="servnotes">
                            <Form.Label className="sr-only">Equipment Needed / Alternative Format</Form.Label>
                            <Form.Control as="textarea" rows={3} className={this.getErrorClass('servnotes')} type="text" placeholder="Equipment Needed / Alternative Format" value={this.getFormValue('servnotes')} onChange={(e) => this.setFormValue('servnotes', e.target.value)}/>
                        </Form.Group>

                        <Form.Group controlId="persppartinfo">
                            <Form.Label className="sr-only">Suggest Some Good Participants</Form.Label>
                            <Form.Control as="textarea" rows={3} className={this.getErrorClass('persppartinfo')} type="text" placeholder="Can you suggest some good participants? Or a moderator?" value={this.getFormValue('persppartinfo')} onChange={(e) => this.setFormValue('persppartinfo', e.target.value)}/>
                        </Form.Group>

                    </Card.Body>
                    <Card.Footer>
                        <Button variant="primary">Submit</Button>
                    </Card.Footer>
                </Card>
            </Form>
        )
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
        this.setState({
            ...state,
            values: newValue,
            messages: null,
            errors: errors
        });
    }
}

export default SubmissionForm;