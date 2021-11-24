import React, { useState } from 'react';

import Container from 'react-bootstrap/Container';

import Footer from './component/footer'
import SubmissionForm from './component/submissionForm';

import './scss/brainstorm.scss';

const BrainstormApp = () => (
    <Container>
        <header className="pb-3 mb-2">
            <img className="w-100" src="/HeaderImage.php" alt="page header" />
        </header>
        <div className="row">
            <section className="col-md-9">
                <SubmissionForm />
            </section>
            <section className="col-md-3">
                <p><b>Submissions are open for programming for WisCon 2022.</b></p>
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