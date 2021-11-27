import React, { useState } from 'react';

import Container from 'react-bootstrap/Container';

import Footer from './component/footer';
import MainBody from './component/mainBody';
import PageHeader from './component/header';

import './scss/brainstorm.scss';

const BrainstormApp = () => (
    <Container>
        <PageHeader />
        <div className="row">
            <section className="col-md-9">
                <MainBody />
            </section>
            <section className="col-md-3">
                <div className="card mb-3">
                    <div className="card-body">
                        Submissions are open until <b>Jan 7th, 2022</b>.
                    </div>
                </div>

                <p>What are we looking for? WisCon encourages programming that has at least implicit,
                    but preferrably explicit, recognition of and engagement with an expansive definition
                    of feminism. We encourage programming that is attentive to issues of gender, sexuality,
                    race, class, disability, and other issues of oppression and/or identity politics. 
                </p>
            </section>
        </div>
        <Footer />
    </Container>
);

export default BrainstormApp;