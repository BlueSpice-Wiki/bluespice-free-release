<?php

namespace BlueSpice\CountThings;

/**
 * CountThings adds 3 tags, used in WikiMarkup as follows:
 * absolute number of articles: <bs:countarticles />
 *
 * Count of Characters, Words, and Pages (2000 chars/page) for article 'Test':
 * <bs:countcharacters>Test</bs:countcharacters>
 *
 * <bs:countcharacters modes="words chars">Test</bs:countcharacters> shows only
 * word- and charactercount
 *
 * <bs:countcharacters>Test Test_Site</bs:countcharacters> shows counts for this two sites
 * absolute number of users: <bs:countusers />
 */
class Extension extends \BlueSpice\Extension {
}
