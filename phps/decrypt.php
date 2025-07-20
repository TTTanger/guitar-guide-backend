<?php
// Decrypts a password using a simple Vigenère cipher variant
// Reference: https://en.wikipedia.org/wiki/Vigen%C3%A8re_cipher

/**
 * Decrypt a password that was encrypted with a simple Vigenère cipher variant.
 * This function takes an encrypted string and returns the original plain text password.
 * The decryption uses a static key and only works for printable ASCII characters.
 *
 * @param string $encrypted_psw The encrypted password string
 * @return string The decrypted (plain text) password
 */
function decrypt($encrypted, $time) {
    $key = md5($time); // Get the key from the time
    $encrypted_len = strlen($encrypted); // Length of the encrypted password
    $key_len = strlen($key); // Length of the key
    $decrypted = '';

    // Loop through each character of the encrypted password
    for ($i = 0; $i < $encrypted_len; $i++) {
        $c_char = $encrypted[$i]; // Current character from the encrypted password
        $k_char = $key[$i % $key_len]; // Corresponding character from the key (repeats if needed)

        // Convert characters to offsets (printable ASCII range)
        $c_offset = ord($c_char) - 32;
        $k_offset = ord($k_char) - 32;

        // Vigenère decryption: (cipher - key + 95) mod 95 (printable ASCII)
        $p_offset = ($c_offset - $k_offset + 95) % 95;

        // Convert back to a printable ASCII character and add to result
        $decrypted .= chr($p_offset + 32);
    }

    return $decrypted; // Return the decrypted password
}
?>