import React, { Component } from 'react';

import dayjs from "dayjs";
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import advancedFormat from "dayjs/plugin/advancedFormat"
dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.extend(advancedFormat);

import store from '../state/store';

class DateInfo extends Component {

    constructor(props) {
        super(props);

        this.state = {
            divisions: store.getState().options.divisions || []
        }
    }

    componentDidMount() {
        this.unsubscribe = store.subscribe(() => {
            this.setState({
                divisions: store.getState().options.divisions || [],
            });
        });
    }

    componentWillUnmount() {
        if (this.unsubscribe) {
            this.unsubscribe();
        }
    }

    render() {
        let items = this.state.divisions.map((d) => {return this.formatDivision(d)});

        return (
            <div className="card mb-3">
                <div className="card-body">
                    <h5>Submission Dates</h5>
                    {items}
                </div> 
            </div>
        );
    }
    formatDivision(division) {
        if (division.to_time) {
            let to = dayjs(division.to_time).format("MMM Do, YYYY [at] h:mm a z");
            let tz = dayjs.tz.guess();
            return (
                <div className="mt-2" key={'division' + division.id}>
                    {division.name} <span className="text-muted">open until:</span><br />
                    <b><time dateTime={division.to_time}>{to}</time>.</b>
                </div>
            );
        } else {
            return undefined;
        }
    }
}

export default DateInfo;