# Milosa Social Media Aggregator Twitter Plugin
[![Build Status](https://travis-ci.org/milosa/social-media-aggregator-twitter-plugin.svg?branch=master)](https://travis-ci.org/milosa/social-media-aggregator-twitter-plugin)
[![Coverage Status](https://coveralls.io/repos/github/milosa/social-media-aggregator-twitter-plugin/badge.svg?branch=master)](https://coveralls.io/github/milosa/social-media-aggregator-twitter-plugin?branch=master)

Twitter plugin for [Milosa Social Media Aggregator Bundle](https://github.com/milosa/social-media-aggregator-bundle)
  
## Installation

`composer require milosa/social-media-aggregator-twitter-plugin`

## Usage

Get Twitter API access here: https://developer.twitter.com/en/apply/user
 
Add the following config to your configuration file:

### Sample config file
    milosa_social_media_aggregator:
        plugins:
            twitter:
                auth_data:
                    consumer_key: '%env(TWITTER_CONSUMER_KEY)%'
                    consumer_secret: '%env(TWITTER_CONSUMER_SECRET)%'
                    oauth_token: '%env(TWITTER_OAUTH_TOKEN)%'
                    oauth_token_secret: '%env(TWITTER_OAUTH_TOKEN_SECRET)%'
                enable_cache: true
                cache_lifetime: 3600
                number_of_tweets: 20
                account_to_fetch: FamilyGuyonFOX
                template: twitter.twig