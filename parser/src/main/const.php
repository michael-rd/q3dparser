<?php
/**
 * Created by IntelliJ IDEA.
 * User: Mike
 * Date: 13.02.2018
 * Time: 3:00
 */
define ('Q3_MESSAGE_MAX_SIZE', 0x4000);
define ('Q3_MAX_STRING_CHARS', 1024);
define ('Q3_BIG_INFO_STRING', 8192);
define ('Q3_MAX_CONFIGSTRINGS', 1024);
define ('Q3_PERCENT_CHAR_BYTE', 37);
define ('Q3_DOT_CHAR_BYTE', 46);

final class Q3Const {
    const MAX_CONFIGSTRINGS = 1024;



    const Q3_DEMO_CFG_FIELD_CLIENT = 0;
    const Q3_DEMO_CFG_FIELD_GAME = 1;
    const Q3_DEMO_CFG_FIELD_PLAYER = 544;
}

/**
 * Q3 server commands
 */
final class Q3_SVC {
    const BAD = 0;  // not used in demos
    const NOP = 1;  // not used in demos
    const GAMESTATE = 2;
    const CONFIGSTRING = 3; // only inside gamestate
    const BASELINE = 4;     // only inside gamestate
    const SERVERCOMMAND = 5;
    const DOWNLOAD = 6; // not used in demos
    const SNAPSHOT = 7;
    const EOF = 8;
}

/*
public static final int GENTITYNUM_BITS = 10;
    public static final int MAX_GENTITIES = 1<<GENTITYNUM_BITS;

    public static final int FLOAT_INT_BITS = 13;
    public static final int FLOAT_INT_BIAS = (1<<(FLOAT_INT_BITS-1));

    public static final int PACKET_BACKUP = 32;
    public static final int PACKET_MASK = PACKET_BACKUP-1;

    public static final int MAX_RELIABLE_COMMANDS = 64;

    // q_shared.h
    public static final int MAX_POWERUPS = 16;
    public static final int MAX_WEAPONS = 16;
    public static final int MAX_STATS = 16;
    public static final int MAX_PERSISTANT = 16;
    public static final int PS_PMOVEFRAMECOUNTBITS = 6;
    public static final int MAX_PS_EVENTS = 2;
    public static final int MAX_MAP_AREA_BYTES = 16;

    // cg_public.h
    public static final int CMD_BACKUP = 64;
    public static final int CMD_MASK = CMD_BACKUP-1;


    // client.h
    public static final int MAX_PARSE_ENTITIES = 2048;
    // '%'
    public static final byte PERCENT_CHAR_BYTE = 37;
    public static final byte DOT_CHAR_BYTE = 46;
*/