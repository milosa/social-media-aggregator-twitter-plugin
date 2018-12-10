import React, { Component } from "react";
import moment from "moment";

// const Twitter = (message) => {
export default class Twitter extends Component
{
    render() {
        const { message } = this.props;

        console.log('twitter');
        return (<li className="card mb-4 w-25">
            <article className="card-body">
                <a href={message.URL} rel="noopener noreferrer">
                    <h2 className="card-title"><img
                        src={message.authorThumbnail.replace('_normal', '_mini')}/> {message.author}</h2>
                    <span className="author-name">@{message.screenName}</span>
                </a>
                <small className="time"><a href={message.URL} target="_blank" title=""
                                           rel="noopener noreferrer">{moment(message.date.date).fromNow()}</a></small>
                <p className="card-text"
                   dangerouslySetInnerHTML={{__html: message.parsedBody !== null ? message.parsedBody : message.body}}></p>
            </article>
       </li>);
    }
}