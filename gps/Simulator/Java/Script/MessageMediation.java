import java.util.*;

public class MessageMediation
{
	private String rawMessage;
	private String messageType;
	private String objectCalled;
	
	InsertPosition insertposition;
	InsertAlert insertalert;
	InsertOther inserother;
	

	public MessageMediation(){}

	public String getRawMessage(){ return rawMessage; }
	public String getMessageType(){ return messageType; }
	public String getObjectCalled(){ return objectCalled; }

	public void setRawMessage(String rawMessage){ this.rawMessage = rawMessage; }
	public void setMessageType(String messageType){ this.messageType = messageType; }
	public void setObjectCalled(String objectCalled){ this.objectCalled = objectCalled; }

	public void showRawMessage(){	System.out.println(rawMessage); }
	public void showMessageType(){	System.out.println(messageType); }
	public void showObjectCalled(){	System.out.println(objectCalled); }

	public String toString(){
		return "State of  MessageMediation Object :"
		+"\n\t RawMessage = "+rawMessage 
		+"\n\t MessageType = "+messageType 
		+"\n\t ObjectCalled = "+objectCalled;
	}
	public void insertRecord()
	{
		String sql =  "Insert into MessageMediation(rawMessage,messageType,objectCalled) Values('"+rawMessage+"','"+messageType+"','"+objectCalled+"')";
	}
	public void updateRecord(String whereId, String whereValue)
	{
		String sql =  "Update MessageMediation SET rawMessage='"+rawMessage+"',messageType='"+messageType+"',objectCalled='"+objectCalled+"' Where "+whereId+" = '"+whereValue+"'";
	}
	public void deleteRecord(String whereId, String whereValue)
	{
		String sql =  "Delete From MessageMediation Where "+whereId+" = '"+whereValue+"'";
	}

	public void parseMessage(String message)
	{
		StringTokenizer st = new StringTokenizer(message,"#");		
 		while(st.hasMoreTokens())
 		{
 			String record = st.nextToken()+"#";
 			if(checkMessageType(record))
 			{
 			//	System.out.println("Message inserted of Type : "+messageType);
			}
			else
			{
			//	System.out.println(messageType+" type message not inserted !!!.");
			}
 		}
	}
	public boolean checkMessageType(String message)
	{
		boolean isKnownType = false;
		messageType = "UNKNOWN";
		
		rawMessage = message;	
		if(message.startsWith("$") && message.endsWith("A#"))
		{
			messageType	= "DATA";
			connectDATA();
			isKnownType = true;
			return isKnownType;
		}
		
		if(message.startsWith("$") && message.endsWith("V#"))
		{
			messageType	= "ALERT";
			connectALERT();			
			isKnownType = true;
			return isKnownType;
		}
		
		if(message.startsWith("$"))
		{
			messageType	= "OTHER";
			connectOTHER();			
			isKnownType = true;
			return isKnownType;
		}		
		return isKnownType;
	}
	
	public void connectDB()
	{
		insertposition = new InsertPosition();
		insertposition.connectDB();
		insertposition.preStatement();	
		
		insertalert = new InsertAlert();
		insertalert.connectDB();
		insertalert.preStatement();	
		
		inserother = new InsertOther();
		inserother.connectDB();
		inserother.preStatement();
	}	
		
	public void connectDATA()
  	{			
		insertposition.breakUpString(rawMessage);
		insertposition.insertRecord();
  	}
  	
	public void connectALERT()
  	{  	
		insertalert.breakUpString(rawMessage);
		insertalert.insertRecord();
  	}
  	
	public void connectOTHER()
  	{  			
		inserother.breakUpString(rawMessage);
		inserother.insertRecord();
  	}
  	  	  	
	public static void main(String [] args)
	{
		MessageMediation messagemediation = new MessageMediation();
		messagemediation.connectDB();
		messagemediation.parseMessage("$1,2,04,10,11,15,17,35,26473636S,075497690W,00.0,348,5,0,A#");
		messagemediation.parseMessage("$1,2,28,04,09,05,18,12,000000000,0000000000,00.0,000,0,1,V#");
		messagemediation.parseMessage("$3,2,Ignition On#");
		System.out.println(messagemediation);
	}
}