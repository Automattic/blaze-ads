/* eslint-disable no-console */
/* eslint no-process-exit: 0, no-undef: 0, strict: 0 */
'use strict';

require( 'shelljs/global' );
const chalk = require( 'chalk' );
const archiver = require( 'archiver' );
const fs = require( 'fs' );

const pluginSlug = process.env.npm_package_name;

// some config
const releaseFolder = 'release';
const targetFolder = `${ releaseFolder }/${ pluginSlug }`;
const filesToCopy = [
	'includes',
	'vendor',
	'woo-blaze.php',
	'languages',
	'changelog.txt',
	'readme.txt',
];

// start with a clean release folder
rm( '-rf', releaseFolder );
mkdir( releaseFolder );
mkdir( targetFolder );

// copy the directories to the release folder
cp( '-Rf', filesToCopy, targetFolder );

const output = fs.createWriteStream(
	releaseFolder + '/' + pluginSlug + '.zip'
);
const archive = archiver( 'zip', { zlib: { level: 9 } } );

output.on( 'close', () => {
	console.log(
		chalk.green(
			'All done: Release is built in the ' + releaseFolder + ' folder.'
		)
	);
} );

archive.on( 'error', ( err ) => {
	console.error(
		chalk.red(
			'An error occured while creating the zip: ' +
				err +
				'\nYou can still probably create the zip manually from the ' +
				targetFolder +
				' folder.'
		)
	);
} );

archive.pipe( output );

archive.directory( targetFolder, pluginSlug );

archive.finalize();
