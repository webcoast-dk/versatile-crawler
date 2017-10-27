# EXT:versatile_crawler

Versatile Crawler is basically an extension to crawl pages and content in an TYPO3 CMS installation.
It is developed on the basis of TYPO3 CMS version 8. The extension has a clear and easily understandable
structure and provides queue and crawler functions for pages and records.

## Installation & Setup

Clone the extension from GitHub, manually or via composer, and activate the extension via the extension manager.
Create a crawler configuration record on the page you like to start the indexing on, e.g. on the homepage.
Go the scheduler module and create a queue task and a process task. Configure a cron job that triggers
TYPO3 CMS' scheduler.

### Prerequisites

* TYPO3 CMS 8
* PHP 7 w/ cURL

## Documentation
The documentation can be found in the GitHub wiki: https://github.com/webcoast-dk/versatile-crawler/wiki

## Contributing

Feel free to fork the repository, make changes and create a pull request. If you are not into coding
or do not have the time, open up an issue.

## Credits

The extension is developed and maintained by Thorben Nissen (https://www.kapp-hamburg.de/en/)

## License

Copyright (C) 2017 Thorben Nissen

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
