# DTrade
[![Build Status](https://travis-ci.org/lukasyelle/DTrade.svg?branch=master)](https://travis-ci.org/lukasyelle/DTrade)
[![StyleCI](https://github.styleci.io/repos/163559139/shield?branch=master)](https://github.styleci.io/repos/163559139) 
[![Maintainability](https://api.codeclimate.com/v1/badges/f7a172518dfe07dcd0e8/maintainability)](https://codeclimate.com/github/lukasyelle/DTrade/maintainability)

DTrade is a stock trading assistance application based on the Laravel framework and integrated into Robinhood.

## Goal
The goal of this application is to help take some emotion out of trading. This system will accomplish that goal in a few ways:

1) By providing guidance and interpretation of various indicators.
    1) DTrade takes data from an external source to analyze trends internally and come up with simplified indicators and targets for buy/sell points.
    3) Included in the targets will be a stop loss, and that can be executed automatically under both `Smart Exit` and `AutoTrade` policies.
    
2) By automating the trading process as much (or as little) as you would like.
    1) DTrade, at its core, is a wrapper around Robinhood and will execute trades on this platform on your behalf.
    2) The level of automation is configurable; you will be able to choose between `Manual`, `Smart Entry`, `Smart Exit`, and `AutoTrade`.
    3) No orders will be placed without authorization, but the system will place them for you once approved.
