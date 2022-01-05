import React, { Component } from 'react';
import axios from 'axios';

import Alert from 'react-bootstrap/Alert';
import Button from 'react-bootstrap/Button';
import Form from 'react-bootstrap/Form';
import Modal from 'react-bootstrap/Modal';
import Nav from 'react-bootstrap/Nav';
import Navbar from 'react-bootstrap/Navbar';
import NavDropdown from 'react-bootstrap/NavDropdown';

import store from '../state/store';
import { extractAndDispatchJwt, logout } from '../state/authActions';

class PageHeader extends Component {

    constructor(props) {
        super(props);

        this.state = {
            jwt: store.getState().auth.jwt,
            login: {},
            showModal: !store.getState().auth.jwt && !store.getState().auth.pending
        };
    }

    componentDidMount() {
        this.unsubscribe = store.subscribe(() => {
            this.setState((state) => ({
                ...state,
                jwt: store.getState().auth.jwt,
                showModal: !store.getState().auth.jwt && !store.getState().auth.pending
            }));
        });
    }

    componentWillUnmount() {
        if (this.unsubscribe) {
            this.unsubscribe();
        }
    }
    render() {
        let loginMenu = this.isAuthenticated() 
            ? (<NavDropdown title={this.getName()} id="admin-nav-dropdown">
                <NavDropdown.Item onClick={() => this.logout()}>Logout</NavDropdown.Item>
            </NavDropdown>) 
            : (<Nav.Link onClick={() => this.showLoginModal()}>Login</Nav.Link>);
        let message = (this.state.login.message) ? (<div className="alert alert-danger">{this.state.login.message}</div>) : undefined;
        let loginMessage = (!this.isAuthenticated()) ? (<Alert variant="warning">Please <a className="alert-link" href="https://program.wiscon.net" onClick={(e) => { e.preventDefault(); this.showLoginModal();} }>log in</a> to submit session ideas.</Alert>) : undefined;
        return [
            <header className="mb-3" key="page-header-main">
                <img className="w-100" src="/HeaderImage.php" alt="page header" />
                <Navbar bg="dark" expand="lg" className="navbar-dark navbar-expand-md justify-content-between">
                    <Nav className="navbar-expand-md navbar-dark bg-dark ">
                        <Nav.Link href="/welcome.php" rel="noreferrer">Home</Nav.Link>
                        <Nav.Link href="https://wiscon.net" target="_blank" rel="noreferrer">WisCon</Nav.Link>
                    </Nav>
                    <Nav className="navbar-expand-md navbar-dark bg-dark ml-auto">
                        {loginMenu}
                    </Nav>
                </Navbar>
            </header>,
            <div key="login-message">
                {loginMessage}
            </div>,
            <Modal show={this.state.showModal}  onHide={() => this.handleClose()} key="page-header-login-dialog">
                <Form>
                    <Modal.Header closeButton>
                    <Modal.Title>Login</Modal.Title>
                    </Modal.Header>
                    <Modal.Body>
                        {message}
                        <p>Please log in to submit session ideas.</p>
                        <Form.Group className="mb-3" controlId="formEmail">
                            <Form.Label className="sr-only">Email</Form.Label>
                            <Form.Control type="email" placeholder="Enter email" value={this.state.userid} onChange={(e) => this.setUserid(e.target.value)}/>
                        </Form.Group>
                        <Form.Group className="mb-3" controlId="formPasswod">
                            <Form.Label className="sr-only">Password</Form.Label>
                            <Form.Control type="password" placeholder="Password"  value={this.state.password} onChange={(e) => this.setPassword(e.target.value)}/>
                        </Form.Group>
                    </Modal.Body>
                    <Modal.Footer>
                        <a href="/ForgotPassword.php" className="btn btn-link" target="_blank" rel="noreferrer">Reset password</a>
                        <Button type="submit" variant="primary" onClick={(e) => {e.preventDefault(); this.processLogin();}} disabled={!this.state.login.loginEnabled}>
                            Login
                        </Button>
                    </Modal.Footer>
                </Form>
            </Modal>
        
        ];
    }

    setUserid(userid) {
        let state = this.state;
        let enabled = state.login.loginEnabled;
        if (userid && this.state.login.password) {
            enabled = true;
        } else {
            enabled = false;
        }
        this.setState({
            ...state,
            login: {
                ...state.login,
                userid: userid,
                loginEnabled: enabled,
                message: undefined
            }
        });
    }

    setPassword(value) {
        let state = this.state;
        let enabled = state.login.loginEnabled;
        if (this.state.login.userid && value) {
            enabled = true;
        } else {
            enabled = false;
        }
        this.setState({
            ...state,
            login: {
                ...state.login,
                password: value,
                loginEnabled: enabled,
                message: undefined
            }
        });
    }

    handleClose() {
        this.setState((state) => ({
            ...state, 
            showModal: false
        }));
    }

    showLoginModal() {
        this.setState((state) => ({
            ...state, 
            showModal: true
        }));
    }

    processLogin() {
        axios.post('/api/login.php', {
            userid: this.state.login.userid,
            password: this.state.login.password
        })
        .then(res => {
            extractAndDispatchJwt(res);
            this.handleClose();
        })
        .catch(error => {
            console.log(error);
            let message = "There was a technical problem trying to log you in. Try again later."
            if (error.response && error.response.status === 401) {
                message = "There was a problem with your userid and/or password."
            }
            this.setState((state) => ({
                ...state,
                login: {
                    ...state.login,
                    message: message
                }
            }))
        });
    }

    getName() {
        if (this.isAuthenticated()) {
            let jwt = this.state.jwt;
            let parts = jwt.split('.');
            if (parts.length === 3) {
                let payload = JSON.parse(atob(parts[1]));
                return payload['name'] || "Your Name Here";
            } else {
                return "Your Name Here";
            }
        } else {
            return undefined;
        }
    }

    isAuthenticated() {
        return this.state.jwt;
    }

    logout() {
        store.dispatch(logout());
    }
}


export default PageHeader;