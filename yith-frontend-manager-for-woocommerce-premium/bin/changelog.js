/* jshint ignore: start */

/**
 * This script generate a changelog from git commits
 *
 * @version 1.0.3
 * @author Leanza Francesco <leanzafrancesco@gmail.com>
 */


const { exec } = require( 'child_process' );
const chalk    = require( 'chalk' );
const promptly = require( 'promptly' );

function execShellCommand( cmd ) {
	return new Promise( ( resolve, reject ) => {
		exec( cmd, ( error, stdout, stderr ) => {
			if ( error ) {
				console.warn( error );
			}
			if ( stdout ) {
				resolve( stdout );
			} else {
				reject( stderr );
			}
		} );
	} );
}

const git = ( command ) => execShellCommand( `git ${command}` );

const SPACE      = '\t';
const DONE       = chalk.reset.inverse.bold.green( ' DONE ' );
const ERROR      = chalk.reset.inverse.bold.red( ' ERROR ' );
const WARNING    = chalk.reset.inverse.bold.yellow( ' WARNING ' );
const newVersion = process.env.npm_package_version;


const formattedDate = ( date = false ) => {
	date         = !!date ? new Date( date ) : new Date();
	const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
	const day    = date.getDate();
	const month  = months[ date.getMonth() ];
	const year   = date.getFullYear();

	return `${day} ${month} ${year}`;
};

const validateVersion = ( version ) => {
	version            = version.trim();
	const versionCheck = version.replace( '.', '' );

	return versionCheck && !isNaN( versionCheck ) ? version : false;
};

const parseChangelog = ( text, args ) => {
	const sortedKeys = ['New', 'Update', 'Fix', 'Tweak', 'Security', 'Dev', 'Remove'],
		  today      = formattedDate();

	let changelogObj = {
			New   : [],
			Update: ['Update: YITH plugin framework', 'Update: language files']
		},
		changelog    = `\n= ${newVersion} - Released on ${today} =\n\n`;

	const wpVersion = !!args.wpVersion ? validateVersion( args.wpVersion ) : false;
	const wcVersion = !!args.wcVersion ? validateVersion( args.wcVersion ) : false;

	if ( wpVersion ) {
		changelogObj.New.push( `New: support for WordPress ${wpVersion}` );
	}

	if ( wcVersion ) {
		changelogObj.New.push( `New: support for WooCommerce ${wcVersion}` );
	}

	text.split( '\n' ).forEach( ( line ) => {
		sortedKeys.forEach( ( key ) => {
			if ( line.indexOf( key + ':' ) === 0 ) {
				if ( key in changelogObj ) {
					changelogObj[ key ].push( line );
				} else {
					changelogObj[ key ] = [line];
				}
			}
		} );
	} );

	sortedKeys.forEach( ( key ) => {
		if ( key in changelogObj && changelogObj[ key ].length ) {
			changelog += '* ' + changelogObj[ key ].join( '\n* ' ) + '\n';
		}
	} );

	return changelog;
};

git( 'describe --tags --abbrev=0' )
	.then( async ( tag ) => {
		const version = validateVersion( tag );
		if ( version ) {
			// it's a version tag
			console.log( chalk.green.bold( `Creating Changelog since ${version} for the new version ${newVersion}` ) );

			const wpVersion = await promptly.prompt(
				chalk.blue.bold( '> New WordPress Version?' ) + chalk.gray( ' (leave empty to skip)' ),
				{ default: '' }
			);

			const wcVersion = await promptly.prompt(
				chalk.blue.bold( '> New WooCommerce Version?' ) + chalk.gray( ' (leave empty to skip)' ),
				{ default: '' }
			);

			git( `log --pretty=format:%B ${version}..HEAD` )
				.then( ( changelog ) => {
					console.log( parseChangelog( changelog, { wpVersion, wcVersion } ) );

					if ( version === newVersion ) {
						console.log( WARNING + chalk.yellow( ` The retrieved tag is equal to the new version! Please check if you set the correct version number in package.json\n` ) );
					}
				} ).catch( ( err ) => {
					console.log( ERROR + ' Something went wrong when retrieving the git log. Maybe your latest commit has a tag.' );
					console.log( parseChangelog( '', { wpVersion, wcVersion } ) );
			} );
		} else {
			console.log( chalk.red( `\nError: The latest tag '${tag}' is not a version tag, so you need to manually create the changelog.` ) );
		}
	} )
	.catch( ( err ) => {
		console.log( ERROR + ' Something went wrong when retrieving the tag.' );
		throw err;
	} );