<?php
/**
 * Auction email footer template
 *
 * @author  Carlos Rodríguez <carlos.rodriguez@yithemes.com>
 * @package YITH\Auctions\Templates\Emails
 */

defined( 'ABSPATH' ) || exit;

?>
															</div>
														</td>
													</tr>
												</table>
												<!-- End Content -->
											</td>
										</tr>
									</table>
									<!-- End Body -->
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center" valign="top">
						<!-- Footer -->
						<table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer">
							<tr>
								<td valign="top">
									<table border="0" cellpadding="10" cellspacing="0" width="100%">
										<tr>
											<td colspan="2" valign="middle" id="credit">
												<?php echo '<span>' . esc_html__( 'You received this email because you subscribed for an auction on our site.', 'yith-auctions-for-woocommerce' ) . '</span></br>'; ?>
											</td>
										<tr>
											<td colspan="2" valign="middle" id="credit">
												<div>
													<span class="yith-wcact-unsubscribe-email"><a href="<?php echo esc_url( $unsubscribe_action_url ); ?>" target="_blank"><?php echo wp_kses_post( $unsubscribe_action_text ); ?></a></span>
												</div>
											</td>
										</tr>
										</tr>
									</table>
								</td>
							</tr>
						</table>
						<!-- End Footer -->
					</td>
				</tr>
			</table>
		</div>
	</body>
</html>
