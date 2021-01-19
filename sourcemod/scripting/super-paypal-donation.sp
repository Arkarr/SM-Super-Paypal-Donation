#include <sourcemod>
#include <sdktools>

#pragma semicolon 1
#pragma newdecls required

#define PLUGIN_VERSION "1.1"

#define QUERY_SELECT_ALL_SUCCESS_PAYEMENTS	"SELECT steamid, sourcemod_group FROM payments WHERE status='COMPLETED'"

Handle DB;
Handle CVAR_RconCommand;

public Plugin myinfo = 
{
	name = "[SM] Super Paypal Donation!",
	author = "Arkarr",
	description = "Provide a way for the players to support their favorite servers in exchange of perks",
	version = PLUGIN_VERSION,
	url = "https://forums.alliedmods.net/member.php?u=198439"
};

public void OnPluginStart()
{
	CreateConVar("sm_super_paypal_donation", PLUGIN_VERSION, "Standard plugin version ConVar. Please don't change me!", FCVAR_REPLICATED|FCVAR_NOTIFY|FCVAR_DONTRECORD);
	CVAR_RconCommand = CreateConVar("sm_super_paypal_donation_rcon", "", "Execute this RCON each time a player is eligble for admin rights!");
}

public void OnConfigsExecuted()
{
	SQL_TConnect(DBConnectionResult, "super-paypal-donation");
	
	CreateTimer(15.0, TMR_ScanDatabase, _, TIMER_REPEAT);
}

public Action TMR_ScanDatabase(Handle timer, any none)
{
	RetrieveRecordsFromDatabase();
}

public void RetrieveRecordsFromDatabase()
{
	char dbquery[200];
	Format(dbquery, sizeof(dbquery), QUERY_SELECT_ALL_SUCCESS_PAYEMENTS);
	
	SQL_TQuery(DB, TQuery_LoadedSuccessfulPayments, dbquery);
}

public void TQuery_LoadedSuccessfulPayments(Handle owner, Handle db, const char[] error, any sqlInfo)
{
	if (db == INVALID_HANDLE)
	{
		char err[255];
		SQL_GetError(DB, err, sizeof(err));
		SetFailState(err);
		
		return;
	}
	
	char steamID[45];
	char groupName[45];
	while (SQL_FetchRow(db))
	{
		SQL_FetchString(db, 0, steamID, sizeof(steamID));
		SQL_FetchString(db, 1, groupName, sizeof(groupName));
		
		int client = GetClientBySteamID(steamID);
		
		if(client != -1)
			ProcessVIP(client, groupName);
	}
}

public void ProcessVIP(int client, char[] groupName)
{
	if(GetUserAdmin(client) != INVALID_ADMIN_ID)
		return;
		
	char grpName[45];
	Format(grpName, sizeof(grpName), "donator-%s", groupName);
	
	AdminId admin = CreateAdmin(grpName);
	GroupId grpId = FindAdmGroup(groupName);
	
	int flags = grpId.GetFlags();
	
	AdminFlag adminflags;
	BitToFlag(flags, adminflags);
	
	admin.SetFlag(adminflags, true);
	
	SetUserAdmin(client, admin, true);
	
	char rcon[300];
	GetConVarString(CVAR_RconCommand, rcon, sizeof(rcon));
	
	if(strlen(rcon) > 1)
	{
		char clientID[20];
		char steamID[50];
		IntToString(GetClientUserId(client), clientID, sizeof(clientID));
		Format(clientID, sizeof(clientID), "#%s", clientID);
		
		GetClientAuthId(client, AuthId_Steam2, steamID, sizeof(steamID));
		
		ReplaceString(rcon, sizeof(rcon), "[PLAYER]", clientID, true);
		ReplaceString(rcon, sizeof(rcon), "[STEAMID]", steamID, true);
		ReplaceString(rcon, sizeof(rcon), "[PACKAGEID]", groupName, true);
		
		ServerCommand(rcon);
	}
	
	PrintToChat(client, "Thanks for your donation.");
}

public int GetClientBySteamID(char[] clientSteamID)
{		
	char pSteamID64[100];
	char pSteamID[100];
	for (int i = MaxClients; i > 0; --i)
	{
		if (IsValidClient(i))
		{
			GetClientAuthId(i, AuthId_SteamID64, pSteamID64, sizeof(pSteamID64));
			GetClientAuthId(i, AuthId_Steam2, pSteamID, sizeof(pSteamID));

			if (StrEqual(clientSteamID, pSteamID64) || StrEqual(clientSteamID, pSteamID))
			{
				return i;
			}
		}
	}
	
	return -1;
}

public bool IsValidClient(int client)
{
	if (client <= 0 || client > MaxClients) return false;
	if (!IsClientInGame(client)) return false;
	if (IsClientSourceTV(client) || IsClientReplay(client)) return false;
	return true;
}

//Database init
public void DBConnectionResult(Handle owner, Handle hndl, const char[] error, any data)
{
	if (hndl == INVALID_HANDLE)
	{
		SetFailState(error);
	}
	else
	{
		DB = hndl;
		RetrieveRecordsFromDatabase();
	}
}