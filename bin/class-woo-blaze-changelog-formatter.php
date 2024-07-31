<?php
/**
 * Jetpack Changelogger Formatter for Woo Blaze
 *
 *  @package Automattic\WooBlaze
 */

use Automattic\Jetpack\Changelog\Changelog;
use Automattic\Jetpack\Changelog\KeepAChangelogParser;
use Automattic\Jetpack\Changelogger\PluginTrait;
use Automattic\Jetpack\Changelogger\FormatterPlugin;

/**
 * Jetpack Changelogger Formatter for Woo Blaze
 *
 * Class WooBlaze_Changelog_Formatter
 */
class Woo_Blaze_Changelog_Formatter extends KeepAChangelogParser implements FormatterPlugin {
	use PluginTrait;

	/**
	 * Bullet for changes.
	 *
	 * @var string
	 */
	private $bullet = '*';

	/**
	 * Output date format.
	 *
	 * @var string
	 */
	private $date_format = 'Y-m-d';

	/**
	 * Title for the changelog.
	 *
	 * @var string
	 */
	private $title = '*** Blaze Ads Changelog ***';

	/**
	 * Separator used in headings and change entries.
	 *
	 * @var string
	 */
	private $separator = '-';

	/**
	 * Modified version of parse() from KeepAChangelogParser.
	 *
	 * @param string $changelog Changelog contents.
	 *
	 * @return Changelog
	 * @throws InvalidArgumentException If the changelog data cannot be parsed.
	 */
	public function parse( $changelog ) {
		$ret = new Changelog();

		// Fix newlines and expand tabs.
		$changelog = strtr( $changelog, array( "\r\n" => "\n" ) );
		$changelog = strtr( $changelog, array( "\r" => "\n" ) );
		while ( strpos( $changelog, "\t" ) !== false ) {
			$changelog = preg_replace_callback(
				'/^([^\t\n]*)\t/m',
				function ( $m ) {
					return $m[1] . str_repeat( ' ', 4 - ( mb_strlen( $m[1] ) % 4 ) );
				},
				$changelog
			);
		}

		// Remove title. Check if the first line containing the defined title, and remove it.
		list( $first_line, $remaining ) = explode( "\n", $changelog, 2 );
		if ( false !== strpos( $first_line, $this->title ) ) {
			$changelog = $remaining;
		}

		// Entries make up the rest of the document.
		$entries       = array();
		$entry_pattern = '/^\d{4}-\d{2}-\d{2}\s+[^\n]+\s+(((?!^\d{4}).)+)/ms';
		preg_match_all( $entry_pattern, $changelog, $version_sections );

		foreach ( $version_sections[0] as $section ) {
			$heading_pattern = '/^(\d{4}-\d{2}-\d{2}) - version ([\d.]+)$/m';
			// Parse the heading and create a ChangelogEntry for it.
			preg_match( $heading_pattern, $section, $heading );

			if ( ! count( $heading ) ) {
				throw new InvalidArgumentException( "Invalid heading: $heading" );
			}

			$timestamp = $heading[1];
			$version   = $heading[2];

			try {
				$timestamp = new DateTime( $timestamp, new DateTimeZone( 'UTC' ) );
			} catch ( \Exception $ex ) {
				throw new InvalidArgumentException( "Heading has an invalid timestamp: $heading", 0, $ex );
			}

			if ( strtotime( $heading[1], 0 ) !== strtotime( $heading[1], 1000000000 ) ) {
				throw new InvalidArgumentException( "Heading has a relative timestamp: $heading" );
			}
			$entry_timestamp = $timestamp;

			$entry = $this->newChangelogEntry(
				$version,
				array(
					'timestamp' => $timestamp,
				)
			);

			$entries[] = $entry;
			$content   = trim( preg_replace( $heading_pattern, '', $section ) );

			if ( '' === $content ) {
				// Huh, no changes.
				continue;
			}

			// Now parse all the subheadings and changes.
			while ( '' !== $content ) {
				$changes = array();
				$rows    = explode( "\n", $content );
				foreach ( $rows as $row ) {
					$row          = trim( $row );
					$row          = preg_replace( '/\\' . $this->bullet . '/', '', $row, 1 );
					$row_segments = explode( $this->separator, $row, 2 );
					$significance = trim( strtolower( $row_segments[0] ) );

					$changes[] = array(
						'subheading' => trim( $row_segments[0] ),
						'content'    => trim( $row_segments[1] ),
					);
				}

				foreach ( $changes as $change ) {
					$entry->appendChange(
						$this->newChangeEntry(
							array(
								'subheading' => $change['subheading'],
								'content'    => $change['content'],
								'timestamp'  => $entry_timestamp,
							)
						)
					);
				}
				$content = '';
			}
		}

		$ret->setEntries( $entries );

		return $ret;
	}

	/**
	 * Write a Changelog object to a string.
	 *
	 * @param Changelog $changelog Changelog object.
	 *
	 * @return string
	 */
	public function format( Changelog $changelog ) {
		$ret = '';

		foreach ( $changelog->getEntries() as $entry ) {
			$timestamp    = $entry->getTimestamp();
			$release_date = null === $timestamp ? $this->get_unreleased_date() : $timestamp->format( $this->date_format );

			$ret .= $release_date . ' ' . $this->separator . ' version ' . $entry->getVersion() . "\n";

			$prologue = trim( $entry->getPrologue() );
			if ( '' !== $prologue ) {
				$ret .= "\n$prologue\n\n";
			}

			foreach ( $entry->getChanges() as $change ) {
				$text = trim( $change->getContent() );
				if ( '' !== $text ) {
					$ret .= $this->bullet . ' ' . $change->getSubheading() . ' ' . $this->separator . ' ' . $text . "\n";
				}
			}

			$ret = trim( $ret ) . "\n\n";
		}

		return $this->title . "\n\n" . trim( $ret ) . "\n";
	}
}
