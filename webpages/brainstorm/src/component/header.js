import React, { Component } from 'react';

import Alert from 'react-bootstrap/Alert';
import Nav from 'react-bootstrap/Nav';
import Navbar from 'react-bootstrap/Navbar';
import NavDropdown from 'react-bootstrap/NavDropdown';

import store from '../state/store';
import { logout, showLoginModal } from '../state/authActions';

class PageHeader extends Component {

    constructor(props) {
        super(props);

        this.state = {
            jwt: store.getState().auth.jwt,
            login: {}
        };
    }

    componentDidMount() {
        this.unsubscribe = store.subscribe(() => {
            this.setState((state) => ({
                ...state,
                jwt: store.getState().auth.jwt
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
            </div>
        ];
    }

    showLoginModal() {
        store.dispatch(showLoginModal());
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