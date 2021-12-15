<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

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
							<tr>
								<td align="center" valign="top">
									<!-- Footer -->
									<table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer">
										<tr>
											<td valign="top">
												<table border="0" cellpadding="10" cellspacing="0" width="100%">
													<tr>
														<td colspan="2" valign="middle" id="credit">
															<?php
                                                            printf(
                                                                __( 'Copyright &copy; %s <a href="%s" target="_blank" style="text-decoration: none; border-bottom: 1px solid #d5d5d5; color: silver;">%s</a>
                                                                <br><br>
                                                                <a href="%s" target="_blank" style="text-decoration: none; font-size: 18px; color: silver;">Powered by %s</a>', 'pqc' ),
                                                                current_time( 'Y' ),
                                                                get_bloginfo( 'url' ),
                                                                get_bloginfo( 'name' ),
                                                                PQC_AUTHOR_URI,
                                                                PQC_NAME
                                                            );
                                                            ?>
														</td>
													</tr>
												</table>
											</td>
										</tr>

									</table>
									<!-- End Footer -->
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
	</body>
</html>
